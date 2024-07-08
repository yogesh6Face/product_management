@extends('layouts.app')

@section('content')
    <h2 class="mt-4">Products</h2>
    <button class="btn btn-primary mb-4" id="addProductBtn">Add Product</button>
    <table class="table table-bordered" id="productsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Description</th>
                <th>Images</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data will be populated by DataTables -->
        </tbody>
    </table>

    <!-- Add/Edit Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="productForm" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="productId" name="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="productName">Name</label>
                            <input type="text" class="form-control" id="productName" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label for="productPrice">Price</label>
                            <input type="number" class="form-control" id="productPrice" name="product_price" required>
                        </div>
                        <div class="form-group">
                            <label for="productDescription">Description</label>
                            <textarea class="form-control" id="productDescription" name="product_description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="productImages">Images</label>
                            <input type="file" class="form-control" id="productImages" name="product_images[]" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('products.index') }}',
            error: function(xhr, error, code) {
                console.log(xhr);
                console.log(code);
            }
        },
        columns: [
                    { data: 'id', name: 'id' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'product_price', name: 'product_price' },
                    { data: 'product_description', name: 'product_description' },
                    {
                        data: 'product_images',
                        name: 'product_images',
                        render: function(data) {
                            // Check if data is null or not an array
                            if (!data || !Array.isArray(data)) {
                                return '';
                            }
                            
                            // Render images
                            var imageHtml = '';
                            data.forEach(function(img) {
                                imageHtml += '<img src="/storage/' + img + '" width="50">';
                            });
                            return imageHtml;
                        }
                    },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false,
                        render: function(data) {
                            // Assuming action is already rendered as HTML buttons
                            return data;
                        }
                    }
                ]

    });

    // Add Product Button Click
    $('#addProductBtn').click(function() {
        $('#productForm')[0].reset();
        $('#productId').val('');
        $('#productModalLabel').text('Add Product');
        $('#productModal').modal('show');
    });

    // Edit Product Button Click
    $('#productsTable').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        $.get('/products/' + id + '/edit', function(data) {
            $('#productId').val(data.id);
            $('#productName').val(data.product_name);
            $('#productPrice').val(data.product_price);
            $('#productDescription').val(data.product_description);
            $('#productModalLabel').text('Edit Product');
            $('#productModal').modal('show');
        });
    });

    // Delete Product Button Click
    $('#productsTable').on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to delete this product?')) {
            $.ajax({
                url: '/products/' + id,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    table.ajax.reload();
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
    });

    // Submit Product Form
    $('#productForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var url = '{{ route('products.store') }}'; // Correctly pointing to the store route
        var type = 'POST'; // Always POST for create

        $.ajax({
            url: url,
            type: type,
            data: formData,
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function() {
                $('#productModal').modal('hide');
                table.ajax.reload();
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
});
</script>
@endsection

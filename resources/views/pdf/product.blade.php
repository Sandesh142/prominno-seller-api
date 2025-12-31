<!DOCTYPE html>
<html>
<head>
    <title>Product Details</title>
    <style>
        body { font-family: sans-serif; }
        .product-header { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .brands-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .brands-table th, .brands-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .brands-table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="product-header">
        <h1>{{ $product->name }}</h1>
        <p><strong>Description:</strong> {{ $product->description }}</p>
    </div>

    <h3>Brands</h3>
    <table class="brands-table">
        <thead>
            <tr>
                <th>Brand Name</th>
                <th>Image</th>
                <th>Detail</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($product->brands as $brand)
            <tr>
                <td>{{ $brand->name }}</td>
                <td>
                    @if($brand->image)
                        <img src="{{ public_path('storage/'.$brand->image) }}" alt="{{ $brand->name }}" width="50">
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $brand->detail }}</td>
                <td>{{ $brand->price }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Total Price:</td>
                <td>{{ $totalPrice }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

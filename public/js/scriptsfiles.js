// Set up CSRF token for all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
// Function to parse and format response data
function handleResponseData(response) {
    // Handle string responses
    if (typeof response === 'string') {
        try {
            return JSON.parse(response);
        } catch (e) {
            return { message: response };
        }
    }
    // Handle JSON responses
    return response;
}

// Enhanced show response message function
function showResponseMessage(response, type = 'success') {
    const data = handleResponseData(response);
    let message = '';

    // Handle different response formats
    if (typeof data === 'object') {
        message = data.message || data.error || JSON.stringify(data);
    } else {
        message = String(data);
    }

    // Create alert HTML
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Add alert to container
    $('#alert-container').html(alertHtml);
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);

    // Also log to console for debugging
    console.log('Response:', data);
}

$(document).ready(function() {
    // Message handling
    function handleMessages() {
        $('#success, #message').show().delay(1000).fadeOut('slow');
    }
    handleMessages();

    // Function to format price
    function formatPrice(price) {
        return parseFloat(price || 0).toFixed(2);
    }

    // Function to update cart display
    function updateCartDisplay(cartItem) {
        const cartBody = $('#cart-items');
        const existingRow = $(`tr[data-product-id="${cartItem.product_id}"]`);
        
        const originalPrice = formatPrice(cartItem.price);
        const activePrice = cartItem.active_price ? formatPrice(cartItem.active_price) : 'N/A';
        
        const priceDisplay = cartItem.active_price 
            ? `<span class="strikethrough">${originalPrice}</span>`
            : originalPrice;
        
        const rowHtml = `
            <tr data-product-id="${cartItem.product_id}">
                <td>${cartItem.product_id}</td>
                <td>${cartItem.product_name}</td>
                <td>${cartItem.description}</td>
                <td>${priceDisplay}</td>
                <td>${activePrice}</td>
                <td>${cartItem.quantity}</td>
                <td>
                    <form class="update-cart-form" data-item-id="${cartItem.id}">
                        <div class="form-group">
                            <input type="number" 
                                   min="1" 
                                   name="quantity" 
                                   value="${cartItem.quantity}" 
                                   class="form-control mb-2">
                            <input type="number" 
                                   name="active_price" 
                                   step="0.01" 
                                   placeholder="Enter active price" 
                                   value="${cartItem.active_price || ''}" 
                                   class="form-control">
                        </div>
                        <button class="btn btn-success mt-2 rounded update-cart-btn" type="submit">
                            <i class="fas fa-sync-alt"></i> Update
                        </button>
                    </form>
                </td>
                <td>
                    <button class="btn btn-danger btn-sm delete-item" data-item-id="${cartItem.id}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `;
    
        if (existingRow.length) {
            existingRow.replaceWith(rowHtml);
        } else {
            if (cartBody.find('tr').length === 0) {
                $('.custom-table-container').html(`
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Original Price</th>
                                    <th>Active Price</th>
                                    <th>Quantity</th>
                                    <th>Update Quantity & Active Price</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items"></tbody>
                        </table>
                    </div>
                `);
            }
            $('#cart-items').append(rowHtml);
        }
    
        updateTotalAmount();
    }

    // Helper function to update total amount
    function updateTotalAmount() {
        $.ajax({
            url: '/calculateTotalAmount',
            method: 'GET',
            success: function(response) {
                $('#totalAmount').text(formatPrice(response.total_amount));
            },
            error: function() {
                console.error('Error updating total amount');
            }
        });
    }

    // Load cart items
    function loadCartItems() {
        $.ajax({
            url: '/getCartItems',
            method: 'GET',
            success: function(response) {
                if (response.cartItems && response.cartItems.length > 0) {
                    $('.custom-table-container p').remove();
                    response.cartItems.forEach(function(cartItem) {
                        updateCartDisplay(cartItem);
                    });
                    $('#totalAmount').text(formatPrice(response.total_amount));
                } else {
                    $('.custom-table-container').html('<p>The cart is empty.</p>');
                    $('#totalAmount').text('0.00');
                }
            },
            error: function() {
                alert('Error loading cart items');
            }
        });
    }

    // Handle product selection
    $('#product-select').change(function() {
        const productId = $(this).val();
        const quantity = $('#quantity').val();

        if (productId) {
            $.ajax({
                url: '/addCart',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.cartItem) {
                        updateCartDisplay(response.cartItem);
                    }
                    if (response.message) {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Error adding item to cart');
                }
            });
        }
    });

    // Handle cart item updates - single implementation
    $(document).on('submit', '.update-cart-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const itemId = form.data('item-id');
        
        $.ajax({
            url: `/updateCart/${itemId}`,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    updateCartDisplay(response.cartItem);
                    alert(response.message);
                } else {
                    alert(response.message || 'Error updating cart');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Error updating cart');
            }
        });
    });

    // Handle delete item
    $(document).on('click', '.delete-item', function() {
        const itemId = $(this).data('item-id');
        if (confirm('Are you sure you want to remove this item?')) {
            $.ajax({
                url: `/deleteCartItem/${itemId}`,
                method: 'POST',
                success: function(response) {
                    if (response.status === 'success') {
                        $(`button[data-item-id="${itemId}"]`).closest('tr').remove();
                        updateTotalAmount();
                        if ($('#cart-items tr').length === 0) {
                            $('.custom-table-container').html('<p>The cart is empty.</p>');
                        }
                        alert(response.message);
                    } else {
                        alert(response.message || 'Error deleting item');
                    }
                },
                error: function() {
                    alert('Error deleting item');
                }
            });
        }
    });

    // Payment form handling
    $('#payment-form').on('submit', function(e) {
        e.preventDefault();
        const cashGiven = $('#cash_given').val();

        $.ajax({
            url: '/processPayment',
            method: 'GET',
            data: {
                cash_given: cashGiven
            },
            success: function(response) {
                $('#payment-result').html(`<div>Total Amount: ${response.total_amount}<br>Balance: ${response.balance}</div>`);
            },
            error: function(response) {
                const errorMessage = response.responseJSON.error;
                $('#payment-result').html(`<div class="alert alert-danger">${errorMessage}</div>`);
            }
        });
    });

    // Product search functionality
    function fetchProducts() {
        let query = $('#product-search').val();
        let category = $('#category-select').val();
        
        $.ajax({
            url: '/searchProductCart',
            method: 'GET',
            data: { query: query, category: category },
            success: function(data) {
                let options = '<option value="">Select a product</option>';
                data.forEach(product => {
                    options += `<option value="${product.id}">${product.product_name}</option>`;
                });
                $('#product-select').html(options);
            }
        });
    }

    $('#product-search').on('input', fetchProducts);
    $('#category-select').on('change', fetchProducts);

    // Initialize
    loadCartItems();
    updateTotalAmount();
});
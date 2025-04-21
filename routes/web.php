<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MpesaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/viewSales', [CartController::class, 'viewSales']);
    Route::get('/deleteSale{id}', [CartController::class, 'deleteSale']);
    Route::post('/filterSales', [CartController::class, 'filterSales']);
    Route::get('/viewCart', [CartController::class, 'viewCart']);
    Route::get('/viewProduct', [CartController::class, 'viewProduct']);
    Route::post('/addToCartAll', [CartController::class, 'addToCartAll']);
    Route::post('/addToCart/{productId}', [CartController::class, 'addToCart']);
    Route::post('/deleteCartItem/{id}', [CartController::class, 'deleteCartItem']);
    Route::get('/searchProductCart', [CartController::class, 'searchProductCart']);
    Route::get('/related-products', [ProductController::class, 'relatedProducts']);
    Route::post('/addCart', [CartController::class, 'addCart']);
    Route::get('/getCartItems', [CartController::class, 'getCartItems']);
    Route::get('/getCartItem/{productId}', [CartController::class, 'getCartItem']);
    Route::get('/removeFromCart/{cartItemId}', [CartController::class, 'removeFromCart']);
    Route::get('/updateCart/{cartItemId}', [CartController::class, 'updateCart']);
    Route::post('/updateCart/{id}', [CartController::class, 'updateCartItem']);
    Route::post('/clearAllItems', [CartController::class, 'clearAllItems']);
    Route::get('/checkout', [CartController::class, 'checkout']);
    Route::get('/debtItems', [CartController::class, 'debtItems']);
    Route::post('/holdCart', [CartController::class, 'holdCart']);
    Route::get('/calculateTotalAmount', [CartController::class, 'calculateTotalAmount']);
    Route::post('/resumeCart/{cartId}', [CartController::class, 'resumeCart']);
    Route::get('/getHeldCarts', [CartController::class, 'getHeldCarts']);
    Route::post('/deleteCart/{cartId}', [CartController::class, 'deleteCart']);
    Route::get('/createCart/{productId}', [CartController::class, 'createCart']);
    Route::post('/addToDebt', [CartController::class, 'addToDebt']);
    Route::get('/viewDebts', [CartController::class, 'viewDebts']);
    Route::get('debtItems/{debtId}', [CartController::class, 'viewDebtItems']);
    Route::post('/settleDebt', [CartController::class, 'settleDebt']);
    Route::get('/searchSalesCart', [CartController::class, 'searchSalesCart']);
    Route::get('/searchProduct', [CartController::class, 'searchProduct']);
    Route::get('/searchDebt', [CartController::class, 'searchDebt']);
    Route::get('/processPayment', [CartController::class, 'processPayment']);
    Route::get('/stockReports', [CartController::class, 'stockReports']);
   

    Route::get('/home', [CartController::class, 'home']);

    // Category Routes
    Route::get('/view_category', [AdminController::class, 'view_category']);
    Route::post('/add_category', [AdminController::class, 'add_category']);
    Route::get('/delete_category/{id}', [AdminController::class, 'delete_category']);

    // Product Routes
    Route::get('/view_product', [AdminController::class, 'view_product']);
    Route::post('/add_product', [AdminController::class, 'add_product']);
    Route::get('/show_product', [AdminController::class, 'show_product']);
    Route::get('/delete_product/{id}', [AdminController::class, 'delete_product']);
    Route::get('/edit_product/{id}', [AdminController::class, 'edit_product']);
    Route::post('/update_product/{id}', [AdminController::class, 'update_product']);
    Route::post('/importProducts', [AdminController::class, 'importProducts']);
    Route::get('/exportSales', [AdminController::class, 'exportSales']);
    Route::get('/search', [AdminController::class, 'search']);
    Route::get('/searchSales', [AdminController::class, 'searchSales']);
    Route::post('/filterSalesAdmin', [AdminController::class, 'filterSalesAdmin']);
    Route::post('/clearAllproducts', [AdminController::class, 'clearAllproducts']);

    // Sales Routes
    Route::get('/view_sales', [AdminController::class, 'view_sales']);

    // Orders Routes
    Route::get('/show_orders', [AdminController::class, 'show_orders']);
    Route::get('/viewCustomeradmin', [AdminController::class, 'viewCustomeradmin']);
    Route::get('/searchCustomeradmin', [AdminController::class, 'searchCustomeradmin']);
    Route::get('/editCustomeradmin/{id}', [AdminController::class, 'editCustomeradmin']);
    Route::get('/destroyCustomeradmin/{id}', [AdminController::class, 'destroyCustomeradmin']);
    Route::put('/updateCustomeradmin/{id}', [AdminController::class, 'updateCustomeradmin']);

    // Customers Routes
    Route::get('/viewCustomer', [CartController::class, 'viewCustomer']);
    Route::get('/createCustomer', [CartController::class, 'createCustomer']);
    Route::post('/storeCustomer', [CartController::class, 'storeCustomer']);
    Route::get('/showCustomer/{id}', [CartController::class, 'showCustomer']);
    Route::get('/editCustomer/{id}', [CartController::class, 'editCustomer']);
    Route::put('/updateCustomer/{id}', [CartController::class, 'updateCustomer']);
    Route::get('/destroyCustomer/{id}', [CartController::class, 'destroyCustomer']);
    Route::get('/searchCustomer', [CartController::class, 'searchCustomer']);
    Route::get('/searchSupplier', [CartController::class, 'searchSupplier']);

    // Suppliers Routes
    Route::get('/viewSupplier', [CartController::class, 'viewSupplier']);
    Route::get('/createSupplier', [CartController::class, 'createSupplier']);
    Route::post('/storeSupplier', [CartController::class, 'storeSupplier']);
    Route::get('/showSupplier/{id}', [CartController::class, 'showSupplier']);
    Route::get('/editSupplier/{id}', [CartController::class, 'editSupplier']);
    Route::post('/updateSupplier/{id}', [CartController::class, 'updateSupplier']);
    Route::delete('/destroySupplier/{id}', [CartController::class, 'destroySupplier']);

    Route::post('/stkpush', [MpesaController::class, 'stkPush']);
Route::post('/mpesa/callback', [MpesaController::class, 'callback'])->name('mpesa.callback');
});

require __DIR__.'/auth.php';

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class PyramidDataService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.pyramid.url'), '/') . '/data/v1';
    }

    /**
     * Generic GET request
     */
    protected function get(string $endpoint, array $params = []): Response
    {
        return Http::get("{$this->baseUrl}/{$endpoint}", $params);
    }

    /**
     * Generic POST request
     */
    protected function post(string $endpoint, array $data = []): Response
    {
        return Http::post("{$this->baseUrl}/{$endpoint}", $data);
    }

    /**
     * Generic PUT request
     */
    protected function put(string $endpoint, array $data = []): Response
    {
        return Http::put("{$this->baseUrl}/{$endpoint}", $data);
    }

    /**
     * Generic DELETE request
     */
    protected function delete(string $endpoint): Response
    {
        return Http::delete("{$this->baseUrl}/{$endpoint}");
    }

    // ==================== Merchants ====================
    
    public function getMerchants(array $filters = [])
    {
        return $this->get('merchants', $filters)->json();
    }

    public function getMerchant(string $id)
    {
        return $this->get("merchants/{$id}")->json();
    }

    public function createMerchant(array $data)
    {
        return $this->post('merchants', $data)->json();
    }

    public function updateMerchant(string $id, array $data)
    {
        return $this->put("merchants/{$id}", $data)->json();
    }

    public function deleteMerchant(string $id)
    {
        return $this->delete("merchants/{$id}")->json();
    }

    // ==================== Menus ====================
    
    public function getMenus(array $filters = [])
    {
        return $this->get('menus', $filters)->json();
    }

    public function getMenu(string $id)
    {
        return $this->get("menus/{$id}")->json();
    }

    public function getMenusByMerchant(string $merchantId)
    {
        return $this->get("menus/by-merchant/{$merchantId}")->json();
    }

    public function createMenu(array $data)
    {
        return $this->post('menus', $data)->json();
    }

    public function updateMenu(string $id, array $data)
    {
        return $this->put("menus/{$id}", $data)->json();
    }

    public function deleteMenu(string $id)
    {
        return $this->delete("menus/{$id}")->json();
    }

    public function toggleMenuStatus(string $id)
    {
        return $this->post("menus/{$id}/toggle-status")->json();
    }

    // ==================== Menu Categories ====================
    
    public function getMenuCategories(array $filters = [])
    {
        return $this->get('menu-categories', $filters)->json();
    }

    public function getMenuCategory(string $id)
    {
        return $this->get("menu-categories/{$id}")->json();
    }

    public function getMenuCategoriesByMerchant(string $merchantId)
    {
        return $this->get("menu-categories/by-merchant/{$merchantId}")->json();
    }

    public function createMenuCategory(array $data)
    {
        return $this->post('menu-categories', $data)->json();
    }

    public function updateMenuCategory(string $id, array $data)
    {
        return $this->put("menu-categories/{$id}", $data)->json();
    }

    public function deleteMenuCategory(string $id)
    {
        return $this->delete("menu-categories/{$id}")->json();
    }

    // ==================== Orders ====================
    
    public function getOrders(array $filters = [])
    {
        return $this->get('orders', $filters)->json();
    }

    public function getOrder(string $id)
    {
        return $this->get("orders/{$id}")->json();
    }

    public function getOrdersByCustomer(string $customerId)
    {
        return $this->get("orders/by-customer/{$customerId}")->json();
    }

    public function getOrdersByMerchant(string $merchantId)
    {
        return $this->get("orders/by-merchant/{$merchantId}")->json();
    }

    public function createOrder(array $data)
    {
        return $this->post('orders', $data)->json();
    }

    public function updateOrder(string $id, array $data)
    {
        return $this->put("orders/{$id}", $data)->json();
    }

    public function updateOrderStatus(string $id, string $status)
    {
        return $this->post("orders/{$id}/update-status", ['status' => $status])->json();
    }

    public function deleteOrder(string $id)
    {
        return $this->delete("orders/{$id}")->json();
    }

    // ==================== Stores ====================
    
    public function getStores(array $filters = [])
    {
        return $this->get('stores', $filters)->json();
    }

    public function getStore(string $id)
    {
        return $this->get("stores/{$id}")->json();
    }

    public function createStore(array $data)
    {
        return $this->post('stores', $data)->json();
    }

    public function updateStore(string $id, array $data)
    {
        return $this->put("stores/{$id}", $data)->json();
    }

    public function deleteStore(string $id)
    {
        return $this->delete("stores/{$id}")->json();
    }

    public function toggleStoreStatus(string $id)
    {
        return $this->post("stores/{$id}/toggle-status")->json();
    }

    // ==================== Store Orders ====================
    
    public function getStoreOrders(array $filters = [])
    {
        return $this->get('store-orders', $filters)->json();
    }

    public function getStoreOrder(string $id)
    {
        return $this->get("store-orders/{$id}")->json();
    }

    public function getStoreOrdersByStore(string $storeId)
    {
        return $this->get("store-orders/by-store/{$storeId}")->json();
    }

    public function createStoreOrder(array $data)
    {
        return $this->post('store-orders', $data)->json();
    }

    public function updateStoreOrder(string $id, array $data)
    {
        return $this->put("store-orders/{$id}", $data)->json();
    }

    public function deleteStoreOrder(string $id)
    {
        return $this->delete("store-orders/{$id}")->json();
    }

    // ==================== Products ====================
    
    public function getProducts(array $filters = [])
    {
        return $this->get('products', $filters)->json();
    }

    public function getProduct(string $id)
    {
        return $this->get("products/{$id}")->json();
    }

    public function getProductsByStore(string $storeId)
    {
        return $this->get("products/by-store/{$storeId}")->json();
    }

    public function createProduct(array $data)
    {
        return $this->post('products', $data)->json();
    }

    public function updateProduct(string $id, array $data)
    {
        return $this->put("products/{$id}", $data)->json();
    }

    public function deleteProduct(string $id)
    {
        return $this->delete("products/{$id}")->json();
    }

    public function toggleProductStatus(string $id)
    {
        return $this->post("products/{$id}/toggle-status")->json();
    }

    // ==================== Product Categories ====================
    
    public function getProductCategories(array $filters = [])
    {
        return $this->get('product-categories', $filters)->json();
    }

    public function getProductCategory(string $id)
    {
        return $this->get("product-categories/{$id}")->json();
    }

    public function createProductCategory(array $data)
    {
        return $this->post('product-categories', $data)->json();
    }

    public function updateProductCategory(string $id, array $data)
    {
        return $this->put("product-categories/{$id}", $data)->json();
    }

    public function deleteProductCategory(string $id)
    {
        return $this->delete("product-categories/{$id}")->json();
    }

    // ==================== Customers ====================
    
    public function getCustomers(array $filters = [])
    {
        return $this->get('customers', $filters)->json();
    }

    public function getCustomer(string $id)
    {
        return $this->get("customers/{$id}")->json();
    }

    public function createCustomer(array $data)
    {
        return $this->post('customers', $data)->json();
    }

    public function updateCustomer(string $id, array $data)
    {
        return $this->put("customers/{$id}", $data)->json();
    }

    public function deleteCustomer(string $id)
    {
        return $this->delete("customers/{$id}")->json();
    }

    // ==================== Customer Addresses ====================
    
    public function getCustomerAddresses(array $filters = [])
    {
        return $this->get('customer-addresses', $filters)->json();
    }

    public function getCustomerAddress(string $id)
    {
        return $this->get("customer-addresses/{$id}")->json();
    }

    public function getCustomerAddressesByCustomer(string $customerId)
    {
        return $this->get("customer-addresses/by-customer/{$customerId}")->json();
    }

    public function createCustomerAddress(array $data)
    {
        return $this->post('customer-addresses', $data)->json();
    }

    public function updateCustomerAddress(string $id, array $data)
    {
        return $this->put("customer-addresses/{$id}", $data)->json();
    }

    public function deleteCustomerAddress(string $id)
    {
        return $this->delete("customer-addresses/{$id}")->json();
    }

    public function setDefaultCustomerAddress(string $id)
    {
        return $this->post("customer-addresses/{$id}/set-default")->json();
    }

    // ==================== Drivers ====================
    
    public function getDrivers(array $filters = [])
    {
        return $this->get('drivers', $filters)->json();
    }

    public function getDriver(string $id)
    {
        return $this->get("drivers/{$id}")->json();
    }

    public function createDriver(array $data)
    {
        return $this->post('drivers', $data)->json();
    }

    public function updateDriver(string $id, array $data)
    {
        return $this->put("drivers/{$id}", $data)->json();
    }

    public function deleteDriver(string $id)
    {
        return $this->delete("drivers/{$id}")->json();
    }

    // ==================== Users ====================
    
    public function getUsers(array $filters = [])
    {
        return $this->get('users', $filters)->json();
    }

    public function getUser(string $id)
    {
        return $this->get("users/{$id}")->json();
    }

    public function createUser(array $data)
    {
        return $this->post('users', $data)->json();
    }

    public function updateUser(string $id, array $data)
    {
        return $this->put("users/{$id}", $data)->json();
    }

    public function deleteUser(string $id)
    {
        return $this->delete("users/{$id}")->json();
    }

    // ==================== Coupons ====================
    
    public function getCoupons(array $filters = [])
    {
        return $this->get('coupons', $filters)->json();
    }

    public function getCoupon(string $id)
    {
        return $this->get("coupons/{$id}")->json();
    }

    public function createCoupon(array $data)
    {
        return $this->post('coupons', $data)->json();
    }

    public function updateCoupon(string $id, array $data)
    {
        return $this->put("coupons/{$id}", $data)->json();
    }

    public function deleteCoupon(string $id)
    {
        return $this->delete("coupons/{$id}")->json();
    }

    // ==================== Promotions ====================
    
    public function getPromotions(array $filters = [])
    {
        return $this->get('promotions', $filters)->json();
    }

    public function getPromotion(string $id)
    {
        return $this->get("promotions/{$id}")->json();
    }

    public function createPromotion(array $data)
    {
        return $this->post('promotions', $data)->json();
    }

    public function updatePromotion(string $id, array $data)
    {
        return $this->put("promotions/{$id}", $data)->json();
    }

    public function deletePromotion(string $id)
    {
        return $this->delete("promotions/{$id}")->json();
    }

    // ==================== Shipment Orders ====================
    
    public function getShipmentOrders(array $filters = [])
    {
        return $this->get('shipment-orders', $filters)->json();
    }

    public function getShipmentOrder(string $id)
    {
        return $this->get("shipment-orders/{$id}")->json();
    }

    public function createShipmentOrder(array $data)
    {
        return $this->post('shipment-orders', $data)->json();
    }

    public function updateShipmentOrder(string $id, array $data)
    {
        return $this->put("shipment-orders/{$id}", $data)->json();
    }

    public function deleteShipmentOrder(string $id)
    {
        return $this->delete("shipment-orders/{$id}")->json();
    }
}


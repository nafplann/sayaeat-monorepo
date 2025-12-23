<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case READ_DASHBOARD = 'read dashboard';
    case READ_BELANJA_AJA_DASHBOARD = 'read belanja aja dashboard';
    case BROWSE_USERS = 'browse users';
    case READ_USERS = 'read users';
    case EDIT_USERS = 'edit users';
    case ADD_USERS = 'add users';
    case DELETE_USERS = 'delete users';

    case BROWSE_ROLES = 'browse roles';
    case READ_ROLES = 'read roles';
    case EDIT_ROLES = 'edit roles';
    case ADD_ROLES = 'add roles';
    case DELETE_ROLES = 'delete roles';

    case BROWSE_PERMISSIONS = 'browse permissions';
    case READ_PERMISSIONS = 'read permissions';
    case EDIT_PERMISSIONS = 'edit permissions';
    case ADD_PERMISSIONS = 'add permissions';
    case DELETE_PERMISSIONS = 'delete permissions';

    case BROWSE_MESSAGE_TEMPLATE = 'browse message template';
    case READ_MESSAGE_TEMPLATE = 'read message template';
    case EDIT_MESSAGE_TEMPLATE = 'edit message template';
    case ADD_MESSAGE_TEMPLATE = 'add message template';
    case DELETE_MESSAGE_TEMPLATE = 'delete message template';

    case BROWSE_MERCHANTS = 'browse merchants';
    case READ_MERCHANTS = 'read merchants';
    case EDIT_MERCHANTS = 'edit merchants';
    case ADD_MERCHANTS = 'add merchants';
    case DELETE_MERCHANTS = 'delete merchants';

    case BROWSE_MENUS = 'browse menus';
    case READ_MENUS = 'read menus';
    case EDIT_MENUS = 'edit menus';
    case ADD_MENUS = 'add menus';
    case DELETE_MENUS = 'delete menus';
    case IMPORT_MENUS = 'import menus';

    case BROWSE_ADDON_CATEGORIES = 'browse addon categories';
    case READ_ADDON_CATEGORIES = 'read addon categories';
    case EDIT_ADDON_CATEGORIES = 'edit addon categories';
    case ADD_ADDON_CATEGORIES = 'add addon categories';
    case DELETE_ADDON_CATEGORIES = 'delete addon categories';

    case BROWSE_MENU_CATEGORIES = 'browse menu categories';
    case READ_MENU_CATEGORIES = 'read menu categories';
    case EDIT_MENU_CATEGORIES = 'edit menu categories';
    case ADD_MENU_CATEGORIES = 'add menu categories';
    case DELETE_MENU_CATEGORIES = 'delete menu categories';

    case BROWSE_ORDERS = 'browse orders';
    case READ_ORDERS = 'read orders';
    case EDIT_ORDERS = 'edit orders';
    case ADD_ORDERS = 'add orders';
    case DELETE_ORDERS = 'delete orders';

    case BROWSE_STORE_ORDERS = 'browse store orders';
    case READ_STORE_ORDERS = 'read store orders';
    case EDIT_STORE_ORDERS = 'edit store orders';
    case ADD_STORE_ORDERS = 'add store orders';
    case DELETE_STORE_ORDERS = 'delete store orders';

    case BROWSE_SHOPPING_ORDERS = 'browse shopping orders';
    case READ_SHOPPING_ORDERS = 'read shopping orders';
    case EDIT_SHOPPING_ORDERS = 'edit shopping orders';
    case ADD_SHOPPING_ORDERS = 'add shopping orders';
    case DELETE_SHOPPING_ORDERS = 'delete shopping orders';

    case BROWSE_CUSTOMERS = 'browse customers';
    case READ_CUSTOMERS = 'read customers';
    case EDIT_CUSTOMERS = 'edit customers';
    case ADD_CUSTOMERS = 'add customers';
    case DELETE_CUSTOMERS = 'delete customers';

    case BROWSE_DRIVERS = 'browse drivers';
    case READ_DRIVERS = 'read drivers';
    case EDIT_DRIVERS = 'edit drivers';
    case ADD_DRIVERS = 'add drivers';
    case DELETE_DRIVERS = 'delete drivers';

    case READ_SETTINGS = 'read settings';
    case EDIT_SETTINGS = 'edit settings';

    case READ_AUDIT_LOGS = 'read audit logs';
    case BROWSE_AUDIT_LOGS = 'browse audit logs';

    case BROWSE_MAKAN_AJA_ORDERS = 'browse makan-aja orders';
    case UPDATE_MAKAN_AJA_ORDERS = 'update makan-aja orders';

    case BROWSE_KIRIM_AJA_ORDERS = 'browse kirim-aja orders';
    case UPDATE_KIRIM_AJA_ORDERS = 'update kirim-aja orders';

    case BROWSE_MARKET_AJA_ORDERS = 'browse market-aja orders';
    case UPDATE_MARKET_AJA_ORDERS = 'update market-aja orders';

    case BROWSE_PROMOTIONS = 'browse promotions';
    case READ_PROMOTIONS = 'read promotions';
    case EDIT_PROMOTIONS = 'edit promotions';
    case ADD_PROMOTIONS = 'add promotions';
    case DELETE_PROMOTIONS = 'delete promotions';

    case READ_ONGOING_ORDERS = 'read ongoing orders';

    case BROWSE_STORES = 'browse stores';
    case READ_STORES = 'read stores';
    case EDIT_STORES = 'edit stores';
    case ADD_STORES = 'add stores';
    case DELETE_STORES = 'delete stores';

    case BROWSE_PRODUCTS = 'browse products';
    case READ_PRODUCTS = 'read products';
    case EDIT_PRODUCTS = 'edit products';
    case ADD_PRODUCTS = 'add products';
    case DELETE_PRODUCTS = 'delete products';
    case IMPORT_PRODUCTS = 'import products';

    case BROWSE_PRODUCT_CATEGORIES = 'browse product categories';
    case READ_PRODUCT_CATEGORIES = 'read product categories';
    case EDIT_PRODUCT_CATEGORIES = 'edit product categories';
    case ADD_PRODUCT_CATEGORIES = 'add product categories';
    case DELETE_PRODUCT_CATEGORIES = 'delete product categories';

    case BROWSE_PRODUCT_DISCOUNTS = 'browse product discounts';
    case READ_PRODUCT_DISCOUNTS = 'read product discounts';
    case EDIT_PRODUCT_DISCOUNTS = 'edit product discounts';
    case ADD_PRODUCT_DISCOUNTS = 'add product discounts';
    case DELETE_PRODUCT_DISCOUNTS = 'delete product discounts';

    case BROWSE_COUPONS = 'browse coupons';
    case READ_COUPONS = 'read coupons';
    case EDIT_COUPONS = 'edit coupons';
    case ADD_COUPONS = 'add coupons';
    case DELETE_COUPONS = 'delete coupons';
}

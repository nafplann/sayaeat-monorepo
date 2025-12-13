require('../theme/js/vendor');
require('./script');

import Utils from './modules/utils';
import Functions from './modules/functions';
import Auth from './modules/auth';
import Settings from './modules/settings';
import Dashboard from './modules/dashboard';
import Profile from './modules/profile';
import Avatar from './modules/avatar';
import Base from './modules/base';
import Menus from './modules/menus';
import BelanjaAja from './modules/belanja_aja';
import MakanAja from './modules/makan_aja';
import MarketAja from './modules/market_aja';
import KirimAja from './modules/kirim_aja';
import OwnerMakanAjaOrders from './modules/owner_makan_aja_orders';
import OwnerMarketAjaOrders from './modules/owner_market_aja_orders';
import Products from './modules/products';

window.Utils = Utils;
window.Functions = Functions;
window.Auth = Auth;
window.Settings = Settings;
window.Dashboard = Dashboard;
window.Profile = Profile;
window.Avatar = Avatar;
window.Base = Base;
window.Menus = Menus;
window.MakanAja = MakanAja;
window.MarketAja = MarketAja;
window.KirimAja = KirimAja;
window.BelanjaAja = BelanjaAja;
window.OwnerMakanAjaOrders = OwnerMakanAjaOrders;
window.OwnerMarketAjaOrders = OwnerMarketAjaOrders;
window.Products = Products;

Utils.init();

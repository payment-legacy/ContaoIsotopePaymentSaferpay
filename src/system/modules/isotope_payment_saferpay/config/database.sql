-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

--
-- Table `tl_iso_payment_modules`
--

CREATE TABLE `tl_iso_payment_modules` (
  `payment_saferpay_accountid` varchar(16) NOT NULL default '',
  `payment_saferpay_password` varchar(16) NOT NULL default '',
  `payment_saferpay_description` varchar(255) NOT NULL default '',
  `payment_saferpay_billpay` char(1) NOT NULL default '',
  `payment_saferpay_billpay_legalform` varchar(4) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

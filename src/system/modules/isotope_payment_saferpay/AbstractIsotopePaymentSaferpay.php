<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  IMS Internet Marketing Solutions Ltd. 2013
 * @author	 Dominik Zogg <dz@erfolgreiche-internetseiten.ch>
 * @package	isotope_payment_saferpay
 * @license	LGPLv3
 */

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Client\FileGetContents;
use Payment\HttpClient\BuzzClient;
use Payment\Saferpay\Saferpay;

abstract class AbstractIsotopePaymentSaferpay extends IsotopePayment
{
	/**
	 * @var Saferpay
	 */
	protected $objSaferpay;

	/**
	 * @var IsotopeOrder
	 */
	protected $objOrder;

    const CLIENT_CURL = 'curl';
    const CLIENT_FOPEN = 'fopen';

	/**
	 * @return Saferpay
	 */
	protected function getSaferpay()
	{
		if(is_null($this->objSaferpay))
		{
			// initialize saferpay
			$this->objSaferpay = new Saferpay();

			if($this->payment_saferpay_httpclient == AbstractIsotopePaymentSaferpay::CLIENT_CURL)
			{
				$client = new Curl();
			}
			elseif($this->payment_saferpay_httpclient == AbstractIsotopePaymentSaferpay::CLIENT_FOPEN)
			{
				$client = new FileGetContents();
			}
			else
			{
				throw new \Exception("No client selected in configuration");
			}

			if($this->payment_saferpay_disable_ssl_verification)
			{
				$client->setVerifyPeer(false);
			}

			$client->setTimeout($this->payment_saferpay_timeout);

			$browser = new Browser($client);

			$buzzClient = new BuzzClient();
			$buzzClient->setClient($browser);

			// set httpclient
			$this->objSaferpay->setHttpClient($buzzClient);

			// set logger
			$this->import('IsotopePaymentLogger');
			$this->objSaferpay->setLogger($this->IsotopePaymentLogger);
		}

		return $this->objSaferpay;
	}

	/**
	 * @return Isotope
	 */
	protected function getIsotope()
	{
		return $this->Isotope;
	}

	/**
	 * @return IsotopeCart
	 */
	protected function getCart()
	{
		return $this->getIsotope()->Cart;
	}

	/**
	 * @return IsotopeConfig
	 */
	protected function getConfig()
	{
		return $this->getIsotope()->Config;
	}

	/**
	 * @return IsotopeOrder
	 */
	protected function getOrder()
	{
		if(is_null($this->objOrder))
		{
			$this->objOrder = new IsotopeOrder();
			if(!$this->objOrder->findBy('cart_id', $this->getCart()->id))
			{
				// if there is no order in this cart something went definitly wrong
				$this->redirect($this->addToUrl('step=failed', true));
			}
		}
		return $this->objOrder;
	}

	/**
	 * @return IsotopeAddressModel
	 */
	protected function getBillingAddress()
	{
		return $this->getCart()->billingAddress;
	}

	/**
	 * @return IsotopeAddressModel
	 */
	protected function getShippingAdress()
	{
		$shippingAddress = $this->getCart()->shippingAddress;
		if($shippingAddress->id != -1)
		{
			return $shippingAddress;
		}
		return $this->getBillingAddress();
	}

	/**
	 * @param $salutation
	 * @return string
	 */
	protected function getGender($salutation)
	{
		switch ($salutation) {
			case 'Herr':
			case 'Mr':
			case 'Mr.':
				return 'm';
			case 'Frau':
			case 'Mrs':
			case 'Mrs.':
				return 'f';
			default:
				return '';
		}
	}
}

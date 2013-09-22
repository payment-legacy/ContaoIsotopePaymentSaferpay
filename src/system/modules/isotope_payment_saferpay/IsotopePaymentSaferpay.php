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

use Payment\HttpClient\BuzzClient;
use Payment\Saferpay\Saferpay;
use Payment\Saferpay\Data\PayInitParameter;
use Payment\Saferpay\Data\PayConfirmParameter;
use Payment\Saferpay\Data\PayCompleteParameter;
use Payment\Saferpay\Data\PayCompleteResponse;
use Payment\Saferpay\Data\Collection\Collection;
use Payment\Saferpay\Data\Billpay\BillpayPayInitParameter;
use Payment\Saferpay\Data\Billpay\BillpayPayConfirmParameter;
use Payment\Saferpay\Data\Billpay\BillpayPayCompleteParameter;
use Payment\Saferpay\Data\Billpay\BillpayPayCompleteResponse;

class IsotopePaymentSaferpay extends IsotopePayment
{
	/**
	 * @var Saferpay
	 */
	protected $objSaferpay;

	/**
	 * @var IsotopeOrder
	 */
	protected $objOrder;

	/**
	 * @return string
	 */
	public function checkoutForm()
	{
		$objPayInitParameter = new Collection;

		$objBasePayInitParameter = new PayInitParameter;
		$objBasePayInitParameter
			->setAmount(round($this->getCart()->grandTotal * 100, 0))
			->setCurrency($this->getConfig()->currency)
			->setAccountid($this->payment_saferpay_accountid)
			->setDescription(urlencode($this->payment_saferpay_description))
			->setOrderid($this->getOrder()->id)
			->setSuccesslink($this->Environment->base . $this->addToUrl('step=complete', true))
			->setFaillink($this->Environment->base . $this->addToUrl('step=failed', true))
			->setBacklink($this->Environment->base . $this->addToUrl('step=failed', true))
			->setGender($this->getGender($this->getBillingAddress()->salutation))
			->setFirstname($this->getBillingAddress()->firstname)
			->setLastname($this->getBillingAddress()->lastname)
			->setStreet($this->getBillingAddress()->street_1)
			->setZip($this->getBillingAddress()->postal)
			->setCity($this->getBillingAddress()->city)
			->setCountry(strtoupper($this->getBillingAddress()->country))
			->setLangid(strtoupper($this->getBillingAddress()->country))
			->setPhone($this->getBillingAddress()->phone)
			->setEmail($this->getBillingAddress()->email)
		;
		$objPayInitParameter->addCollectionItem($objBasePayInitParameter);

		if($this->payment_saferpay_billpay)
		{
			$objBillpayPayInitParameter = new BillpayPayInitParameter;
			$objBillpayPayInitParameter
				->setLegalform($this->payment_saferpay_billpay_legalform)
				->setAddressAddition($this->getBillingAddress()->street_2)
				->setDeliveryGender($this->getGender($this->getDeliveryAddress()->salutation))
				->setDeliveryFirstname($this->getDeliveryAddress()->firstname)
				->setDeliveryLastname($this->getDeliveryAddress()->lastname)
				->setDeliveryStreet($this->getDeliveryAddress()->street_1)
				->setDeliveryAddressAddition($this->getDeliveryAddress()->street_2)
				->setDeliveryZip($this->getDeliveryAddress()->postal)
				->setDeliveryCity($this->getDeliveryAddress()->city)
				->setDeliveryCountry($this->getDeliveryAddress()->country)
				->setDeliveryPhone($this->getDeliveryAddress()->phone)
			;
			$objPayInitParameter->addCollectionItem($objBillpayPayInitParameter);
		}

		$strUrl = $this->getSaferpay()->createPayInit($objPayInitParameter);

		// if something went wrong
		if(!$strUrl)
		{
			$this->log('Payment not successfull', 'PaymentSaferpay checkoutForm()', TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}

		// redirect to saferpay
		$this->redirect($strUrl);
	}

	/**
	 * @return bool
	 */
	public function processPayment()
	{
		$objPayConfirmParameter = new Collection;
		$objPayConfirmParameter->addCollectionItem(new PayConfirmParameter);
		if($this->payment_saferpay_billpay)
		{
			$objPayConfirmParameter->addCollectionItem(new BillpayPayConfirmParameter);
		}

		$payConfirmParameter = $this->getSaferpay()->verifyPayConfirm(
			$_REQUEST['DATA'],
			$this->Input->get('SIGNATURE'),
			$objPayConfirmParameter
		);

		$objPayCompleteParameter = new Collection;
		$objPayCompleteParameter->addCollectionItem(new PayCompleteParameter);
		if($this->payment_saferpay_billpay)
		{
			$objPayCompleteParameter->addCollectionItem(new BillpayPayCompleteParameter);
		}

		$objPayCompleteResponse = new Collection;
		$objPayCompleteResponse->addCollectionItem(new PayCompleteResponse);
		if($this->payment_saferpay_billpay)
		{
			$objPayCompleteResponse->addCollectionItem(new BillpayPayCompleteResponse);
		}

		if($payConfirmParameter->get('AMOUNT') == round($this->getCart()->grandTotal * 100, 0) &&
		   $payConfirmParameter->get('CURRENCY') == $this->getConfig()->currency)
		{
			$this->getSaferpay()->payCompleteV2(
				$payConfirmParameter,
				'Settlement',
				$this->payment_saferpay_password,
				$objPayCompleteParameter,
				$objPayCompleteResponse
			);

			$this->getOrder()->date_paid = time();
			$this->getOrder()->save();
			return true;
		} else {
			$this->getSaferpay()->payCompleteV2(
				$payConfirmParameter,
				'Cancel',
				$this->payment_saferpay_password,
				$objPayCompleteParameter,
				$objPayCompleteResponse
			);
			$this->log('Payment not successfull', 'PaymentSaferpay processPayment()', TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}
	}

	/**
	 * @return Saferpay
	 */
	protected function getSaferpay()
	{
		if(is_null($this->objSaferpay))
		{
			// initialize saferpay
			$this->objSaferpay = new Saferpay();

			// set httpclient
			$this->objSaferpay->setHttpClient(new BuzzClient());
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
	protected function getDeliveryAddress()
	{
		return $this->getCart()->deliveryAddress;
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
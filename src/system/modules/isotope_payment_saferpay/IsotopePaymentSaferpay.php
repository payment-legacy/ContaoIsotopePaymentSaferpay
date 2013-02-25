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
 * @author     Dominik Zogg <dz@erfolgreiche-internetseiten.ch>
 * @package    isotope_payment_saferpay
 * @license    LGPLv3
 */

use Payment\HttpClient\BuzzClient;
use Payment\Saferpay\Saferpay;

class IsotopePaymentSaferpay extends IsotopePayment
{
	/**
	 * @var Saferpay
	 */
	protected $objSaferpay;

	/**
	 * @var string
	 */
	protected $strSessionKey = 'payment.saferpay.data';

	/**
	 * @var IsotopeOrder
	 */
	protected $objOrder;

	/**
	 * @return string
	 */
	public function checkoutForm()
	{
		$strUrl = $this->getSaferpay()->initPayment($this->getSaferpay()->getKeyValuePrototype()->all(array(
			'AMOUNT' => round($this->getCart()->grandTotal * 100, 0),
			'CURRENCY' => $this->getConfig()->currency,
			'ACCOUNTID' => $this->payment_saferpay_accountid,
			'DESCRIPTION' => urlencode($this->payment_saferpay_description),
			'ORDERID' => $this->getOrder()->id,
			'SUCCESSLINK' => $this->Environment->base . $this->addToUrl('step=complete'),
			'FAILLINK' => $this->Environment->base . $this->addToUrl('step=failed'),
			'BACKLINK' => $this->Environment->base . $this->addToUrl('step=failed'),
			'GENDER' => $this->getGender(),
			'FIRSTNAME' => $this->getBillingAddress()->firstname,
			'LASTNAME' => $this->getBillingAddress()->lastname,
			'STREET' => $this->getBillingAddress()->street_1,
			'ZIP' => $this->getBillingAddress()->postal,
			'CITY' => $this->getBillingAddress()->city,
			'COUNTRY' => strtoupper($this->getBillingAddress()->country),
			'LANGID' => strtoupper($this->getBillingAddress()->country),
			'EMAIL' => $this->getBillingAddress()->email,
		)));

		// write to session
		$this->getSession()->set($this->strSessionKey, $this->getSaferpay()->getData());

		// if something went wrong
		if(!$strUrl)
		{
			$this->log('Payment not successfull', 'PaymentSaferpay checkoutForm()', TL_ERROR);
			$this->redirect($this->addToUrl('step=failed', true));
		}

		// html redirect
		$GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="0; URL=' . $strUrl . '">';

		// for those, the redirect doesn't work
		return '<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_payment_saferpay'][0] . '</h2>
		<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_payment_saferpay'][1] . '</p>
		<p><a href="' . $strUrl . '">' . $GLOBALS['TL_LANG']['MSC']['pay_with_payment_saferpay'][2]. '</a></p>';
	}

	/**
	 * @return bool
	 */
	public function processPayment()
	{
		if($this->getSaferpay()->confirmPayment($_GET['DATA'], $this->Input->get('SIGNATURE')) != '')
		{
			if($this->getSaferpay()->completePayment() != '')
			{
				$this->getOrder()->date_paid = time();
				$this->getOrder()->save();
				$this->getSession()->set($this->strSessionKey, null);
				return true;
			}
		}

		$this->log('Payment not successfull', 'PaymentSaferpay processPayment()', TL_ERROR);
		$this->redirect($this->addToUrl('step=failed', true));
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

			// get config as an array
			$arrConfig = $this->objSaferpay->getSaferpayConfig();

			// update config
			$this->objSaferpay->getConfig()->setInitUrl($arrConfig['urls']['init']);
			$this->objSaferpay->getConfig()->setConfirmUrl($arrConfig['urls']['confirm']);
			$this->objSaferpay->getConfig()->setCompleteUrl($arrConfig['urls']['complete']);

			// set validation config
			$this->objSaferpay->getConfig()->getInitValidationsConfig()->all($arrConfig['validators']['init']);
			$this->objSaferpay->getConfig()->getConfirmValidationsConfig()->all($arrConfig['validators']['confirm']);
			$this->objSaferpay->getConfig()->getCompleteValidationsConfig()->all($arrConfig['validators']['complete']);

			// set default config
			$this->objSaferpay->getConfig()->getInitDefaultsConfig()->all($arrConfig['defaults']['init']);
			$this->objSaferpay->getConfig()->getConfirmDefaultsConfig()->all($arrConfig['defaults']['confirm']);
			$this->objSaferpay->getConfig()->getCompleteDefaultsConfig()->all($arrConfig['defaults']['complete']);

			// set httpclient
			$this->objSaferpay->setHttpClient(new BuzzClient());

			// read from session
			$this->getSaferpay()->setData($this->getSession()->get($this->strSessionKey));
		}
		return $this->objSaferpay;
	}

	/**
	 * @return Session
	 */
	protected function getSession()
	{
		return Session::getInstance();
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
	 * @return string
	 */
	protected function getGender()
	{
		switch ($this->getBillingAddress()->salutation) {
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
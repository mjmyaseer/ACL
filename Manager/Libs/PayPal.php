<?php

namespace Manager\Libs;

use Illuminate\Http\Request;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\Plan;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\PayerInfo;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use Illuminate\Support\Facades\Input;
use Redirect;
use URL;

class PayPal
{

    public function __construct()
    {
         /** PayPal api context **/
         $paypal_conf = \Config::get('paypal');
         $this->_api_context = new ApiContext(new OAuthTokenCredential(
             $paypal_conf['client_id'],
             $paypal_conf['secret'])
         );
         $this->_api_context->setConfig($paypal_conf['settings']);
    }
    public function payWithpaypal($amount, $payee)
    {
        dd($amount);
      $amountToBePaid = amount;
      $payer = new Payer();
      $payer->setPaymentMethod('paypal');
  
      $item_1 = new Item();
      $item_1->setName('Mobile Payment') /** item name **/
              ->setCurrency('USD')
              ->setQuantity(1)
              ->setPrice($amountToBePaid); /** unit price **/
  
      $item_list = new ItemList();
      $item_list->setItems(array($item_1));
  
      $amount = new Amount();
      $amount->setCurrency('USD')
             ->setTotal($amountToBePaid);
      $redirect_urls = new RedirectUrls();
      /** Specify return URL **/
      $redirect_urls->setReturnUrl(URL::route('payment_status'))
                ->setCancelUrl(URL::route('payment_status'));
      
      $transaction = new Transaction();
      $transaction->setAmount($amount)
              ->setItemList($item_list)
              ->setDescription('Your transaction description');   
   
      $payment = new Payment();
      $payment->setIntent('Sale')
              ->setPayer($payer)
              ->setRedirectUrls($redirect_urls)
              ->setTransactions(array($transaction));
      try {
           $payment->create($this->_api_context);
      } catch (\PayPal\Exception\PPConnectionException $ex) {
           if (\Config::get('app.debug')) {
              \Session::put('error', 'Connection timeout');
              return Redirect::route('/');
           } else {
              \Session::put('error', 'Some error occur, sorry for inconvenient');
              return Redirect::route('/');
           }
      }
foreach ($payment->getLinks() as $link) {
        if ($link->getRel() == 'approval_url') {
           $redirect_url = $link->getHref();
           break;
        }
      }
      
      /** add payment ID to session **/
      \Session::put('paypal_payment_id', $payment->getId());
if (isset($redirect_url)) {
         /** redirect to paypal **/
         return Redirect::away($redirect_url);
      }
  
      \Session::put('error', 'Unknown error occurred');
      return Redirect::route('/');
    }
    
    public function getPaymentStatus(Request $request)
    {
      /** Get the payment ID before session clear **/
      $payment_id = Session::get('paypal_payment_id');
      /** clear the session payment ID **/
      Session::forget('paypal_payment_id');
      if (empty($request->PayerID) || empty($request->token)) {
         session()->flash('error', 'Payment failed');
         return Redirect::route('/');
      }
      $payment = Payment::get($payment_id, $this->_api_context);
      $execution = new PaymentExecution();
      $execution->setPayerId($request->PayerID);
      /**Execute the payment **/
      $result = $payment->execute($execution, $this->_api_context);
      
      if ($result->getState() == 'approved') {
         session()->flash('success', 'Payment success');
         return Redirect::route('/');
      }
      session()->flash('error', 'Payment failed');
      return Redirect::route('/');
    }

}
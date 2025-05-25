<?php

namespace App\Constants;

class StatusConst
{
    const INCLUSIVE = 'Inclusive'; 
    const EXCLUSIVE = 'Exclusive';
    const ACTIVE = 'Active'; 
    const IN_ACTIVE = 'In-Active';
    
    // Discount types
    const PERCENTAGE = 'Percentage';
    const FIXED = 'Fixed';

    // Payment statuses
    const PAYMENT_STATUS_PAID = 'Paid';
    const PAYMENT_STATUS_FAILED = 'Failed';

    // Order statuses
    const ORDER_STATUS_PENDING = 'Pending';
    const ORDER_STATUS_IN_PROGRESS = 'Progress';
    const ORDER_STATUS_DELIVERED = 'Delivered';
    const ORDER_STATUS_COMPLETED = 'Completed';
    const ORDER_STATUS_CANCEL = 'Cancel';

    // Order types
    const ORDER_TYPE_BUYER = 'Buyer';
    const ORDER_TYPE_SELLER = 'Seller';
    const ORDER_TYPE_RENT = 'Rent';

    const PAYMENT_TYPE_LIST = [
        'Cash On Delivery', 
        'Credit Card', 
        'Debit Card', 
        'Net Banking', 
        'UPI', 
        'Wallet', 
        'Bank Transfer', 
        'EMI', 
        'Cryptocurrency', 
        'Cheque',
        'Stripe',
        'Amazon Pay',
        'Razorpay',
        'PayPal',
        'Google Pay',
        'Apple Pay',
        'Paytm',
        'PhonePe'
    ];

    const PAYMENT_TYPE_COMMENT ='Payment methods including third-party gateways: Cash On Delivery, Credit Card, Debit Card, Net Banking, UPI, Wallet, Bank Transfer, EMI, Cryptocurrency, Cheque, Stripe, Amazon Pay, Razorpay, PayPal, Google Pay, Apple Pay, Paytm, PhonePe';
}

<?php

// Tuition fee
$tuition_fee = 2200000;

// Amount paid
$amount_paid = 2000000;

// Calculate the balance
$balance = $tuition_fee - $amount_paid;

// Classify payment status
if ($amount_paid == 0) {
    $status = "No payment";
} elseif ($amount_paid < $tuition_fee) {
    $status = "Partial payment";
} else {
    $status = "Cleared";
}

// Display results
echo "Tuition Fee: UGX " . number_format($tuition_fee) . "<br>";
echo "Amount Paid: UGX " . number_format($amount_paid) . "<br>";
echo "Balance: UGX " . number_format($balance) . "<br>";
echo "Status: " . $status;

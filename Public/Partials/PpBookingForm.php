<?php
namespace FourDash\Public\Partials;
if (!defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
    <h1>fourdash Booking Form</h1>
    <form id="fourdash-booking-form">
        <label for="customer-name">Customer Name:</label>
        <input type="text" id="customer-name" name="customer-name"><br><br>
        <label for="customer-email">Customer Email:</label>
        <input type="email" id="customer-email" name="customer-email"><br><br>
        <label for="service">Service:</label>
        <select id="service" name="service">
            <!-- Service options will be displayed here -->
        </select><br><br>
        <label for="date">Date:</label>
        <input type="date" id="date" name="date"><br><br>
        <label for="time">Time:</label>
        <input type="time" id="time" name="time"><br><br>
        <button class="button button-primary" id="book-appointment-button">Book Appointment</button>
    </form>
</div>
<?php

/*
 * Author: Aaryan
 * Date Created: 2026-04-19
 * Description: Grader guidance page with quick test instructions and README content.
 */

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Grader Help | Ephesians Dental</title>
  <meta name="description" content="Instructions and grading guide for the Dr. Ruby client website." />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
</head>
<body>
  <nav class="navbar" id="navbar">
    <a href="index.php" class="navbar__logo">Dr. Ruby M. Suresh</a>
    <ul class="navbar__links">
      <li><a href="index.php">Home</a></li>
      <li><a href="index.php#about">About</a></li>
      <li><a href="index.php#reviews">Reviews</a></li>
      <li><a href="index.php#contact">Contact</a></li>
      <li><a href="admin.php">Admin</a></li>
    </ul>
    <div class="navbar__cta">
      <a href="grader_help.php" class="btn-secondary">Grader Help</a>
      <a href="index.php#appointment" class="btn-primary">Book an Appointment</a>
    </div>
  </nav>

  <main class="grader-help">
    <div class="container grader-help__layout">
      <section class="grader-help__readme" aria-label="Project Information">
        <div class="md-content">
          <h1>Dr Ruby M. Suresh - Client Website</h1>

          <h2>Overview</h2>
          <p>This website is for our client Dr. Ruby M. Suresh, a dentist at Ephesians Dental who wanted a personalized dentist website. So thats what we built. She asked us to have a small about section about her and ephesians dental as well as reviews, booking appointments and messaging feature. In the footer there is also contact info, a Google map box to show the location of the clinic.</p>

          <h2>User Side Features</h2>
          <ul>
            <li>Reviews: Users can leaave reviews with their name, their rating out of 5 stars and any text for their review. These reviews are then displayed on the website</li>
            <li>Book An Appointment: Users can book an appointmnet by filling in their email(required), phone number(required), the date and time they want their appointmnet, the appointment type, as well as any additional notes the patient/client may want to include</li>
            <li>Send a Message: The user can send a message to Ruby's email inbox by filling in their Name, Email, Subject and Message (this feature uses PHPMailer)</li>
            <li>Footer Contents: In the footer is some basic ifnormation about the clinic, as well as linked icons going to her facebook and instagram. There is also a goolge mapbox that displays the location</li>
          </ul>

          <h2>Admin Side Features</h2>
          <ul>
            <li>Login: The admin side feaatures can be accessed using the login which is accessed with username ruby@clinic.com and password admin123</li>
            <li>Dashboard: The dashbaord shows Ruby the number of appointments on the current day, the number new patients, the number unread message, the number of confirmed appointmnets and numbner of completed appointments.</li>
            <li>Below these also shows the information of Recent Appointments and Recent Message</li>
            <li>Appointments:
              <ul>
                <li>The admin is able to view all appointments by click appointments on the navbar or the view all appointments button, as well as view the number of pending, confirmed and completed appointments by pressing their cards on the dashboard, or going to the appointments section and manually applying the filter.</li>
                <li>In the appointments section, it shows the following information about the appointments: Name, Email, Phone, Date and Time, Type, Status and Actions</li>
                <li>The Admin can filter by Status, From and To some time and lastly, by Patient Name</li>
                <li>A pending appointment can be be confirmed, declined or rescheduled.</li>
                <li>Once an appointment is confirmed, it awaits to be checked in, reschedule again or declined. <strong>NOTE:</strong> The appointment can only be checked in 30 minutes prior to the appointment time</li>
                <li>Once checked-in, an appointment can be completed, where the dentist can add any aditional notes.</li>
                <li>The notes of completeted appointments can have their notes viewd by clicked the notes button.</li>
              </ul>
            </li>
            <li>Messages:
              <ul>
                <li>Using PHPMailer, the messages go straight to the the inbox in the dashbaord, as well as the actual email inbox.</li>
                <li>If you are a tester of this website, go to actions/send_messages.php and change the username to your gmail and the password to an App Password where you can make here: https://myaccount.google.com/apppasswords</li>
              </ul>
            </li>
          </ul>
        </div>

        <h2 style="margin-top: 24px;">Instructions for Grader</h2>
        <ul class="grader-help__list">
          <li>On Home, test the About, Reviews, and Contact sections and confirm layout responsiveness.</li>
          <li>Submit a review and confirm it appears in the reviews list.</li>
          <li>Book an appointment and test required-field validations and already-booked time protection.</li>
          <li><strong>NOTE:</strong>If you attempt to use the check-in feature, make sure your appointment is within 30 minutes of the booked time.</li>
          <li>Send a contact message and confirm it appears in Admin Messages.</li>
          <li>Open Admin and test appointment actions: confirm, decline, reschedule, check-in, and complete with notes.</li>
          <li>Open Patients and verify patient list with appointment counts.</li>
          <li>Open Messages and test view full message, mark read, and delete actions.</li>
          <li>User Google Passwords and create app password to use in the send_messages.php file to test the email functionality.</li>
          <li>Test responsiveness of user pages on mobile screen widths.</li>
        </ul>
      </section>
    </div>
  </main>
</body>
</html>

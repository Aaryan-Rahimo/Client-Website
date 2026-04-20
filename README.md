<!--
Author: Team Project Group
Date Created: 2026-04-19
Description: Project overview and feature documentation for the clinic web application.
-->

# Dr Ruby M. Suresh - Client Website

## Overview
This website is for our client Dr. Ruby M. Suresh, a dentist at Ephesians Dental who wanted a personalized dentist website. So thats what we built. She asked us to have a small about section about her and ephesians dental as well as reviews, booking appointments and messaging feature. In the footer there is also contact info, a Google map box to show the location of the clinic. 

## User Side Features
- Reviews: Users can leaave reviews with their name, their rating out of 5 stars and any text for their review. These reviews are then displayed on the website
- Book An Appointment: Users can book an appointmnet by filling in their email(required), phone number(required), the date and time they want their appointmnet, the appointment type, as well as any additional notes the patient/client may want to include
- Send a Message: The user can send a message to Ruby's email inbox by filling in their Name, Email, Subject and Message (this feature uses PHPMailer)
- Footer Contents: In the footer is some basic ifnormation about the clinic, as well as linked icons going to her facebook and instagram. There is also a goolge mapbox that displays the location

## Admin Side Features
- Login: The admin side feaatures can be accessed using the login which is accessed with username ruby@clinic.com and password admin123
- Dashboard: The dashbaord shows Ruby the number of appointments on the current day, the number new patients, the number unread message, the number of confirmed appointmnets and numbner of completed appointments.
- Below these also shows the information of Recent Appointments and Recent Message
- Appointments:
  - The admin is able to view all appointments by click appointments on the navbar or the view all appointments button, as well as view the number of pending, confirmed and completed appointments by pressing their cards on the dashboard, or going to the appointments section and manually applying the filter.
  - In the appointments section, it shows the following information about the appointments: Name, Email, Phone, Date and Time, Type, Status and Actions
  - The Admin can filter by Status, From and To some time and lastly, by Patient Name
  - A pending appointment can be be confirmed, declined or rescheduled.
  - Once an appointment is confirmed, it awaits to be checked in, reschedule again or declined. The appointment can only be checked in 30 minutes prior to the appointment time
  - Once checked-in, an appointment can be completed, where the dentist can add any aditional notes.
  - The notes of completeted appointments can have their notes viewd by clicked the notes button.

- Messages:
  - Using PHPMailer, the messages go straight to the the inbox in the dashbaord, as well as the actual email inbox.
  - If you are a tester of this website, go to actions/send_messages.php and change the username to your gmail and the password to an App Password where you can make here: https://myaccount.google.com/apppasswords

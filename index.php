<?php

/*
 * Author: Aaryan, Kissan, Inderbir, Angad
 * Date Created: 2026-04-19
 * Description: The main page for the user side which has the about me, reviews, appoitnment and message forms
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$db = get_db();
ensure_reviews_table($db);

$reviews = $db->query('SELECT * FROM reviews ORDER BY created_at DESC')->fetchAll();
$avgRow  = $db->query('SELECT AVG(rating) AS a, COUNT(*) AS c FROM reviews')->fetch();
$avg     = $avgRow && (int) $avgRow['c'] > 0 ? round((float) $avgRow['a'], 1) : null;
$countRv = $avgRow ? (int) $avgRow['c'] : 0;

function render_stars(int $rating): string
{
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $rating ? '★' : '☆';
    }
    return $out;
}

$timeSlots = appointment_time_slots();
$apptTypes = ['Check-up', 'Cleaning', 'Root Canal', 'Filling', 'Consultation', 'Whitening', 'Emergency', 'Other'];

$errParam     = $_GET['error'] ?? '';
$successParam = $_GET['success'] ?? '';

$bookErr = in_array($errParam, ['missing_fields', 'time_taken'], true) ? $errParam : '';
$bookOk  = ($successParam === '1');

$msgErr = ($errParam === 'msg_missing');
$msgOk  = ($successParam === 'msg');

$reviewErr = ($errParam === 'review');
$reviewOk  = ($successParam === 'review');

$bookOld = (is_array($_SESSION['book_old'] ?? null)) ? $_SESSION['book_old'] : [];
unset($_SESSION['book_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dr. Ruby M. Suresh | Ephesians Dental</title>
  <meta name="description" content="Dr. Ruby M. Suresh — Personal Dentist Profile at Ephesians Dental." />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css" />
  <style>
    .form-alert { padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; font-size: 1.05rem; }
    .form-alert--ok { background: rgba(52,168,83,0.15); color: #1e7e34; }
    .form-alert--err { background: #fdecea; color: #9B1B30; }
  </style>
</head>
<body>

  <nav class="navbar" id="navbar">
    <div class="navbar__logo">Dr. Ruby M. Suresh</div>
    <ul class="navbar__links">
      <li><a href="index.php">Home</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#reviews">Reviews</a></li>
      <li><a href="#contact">Contact</a></li>
      <li><a href="admin.php">Admin</a></li>
    </ul>
    <div class="navbar__cta">
      <a href="grader_help.php" class="btn-secondary">Grader Help</a>
      <a href="#appointment" class="btn-primary">Book an Appointment</a>
    </div>
  </nav>

  <header class="hero" id="hero">
    <div class="container">
      <div class="hero__content">
        <h1>Dr. Ruby M. Suresh</h1>
        <div class="subtitle">Ephesians Dental</div>
        <p>Welcome to Ephesians Dental, where advanced dental care meets a warm, patient-first approach. We're here to help you achieve the healthy, confident smile you deserve.</p>
        <a href="#appointment" class="btn-primary">Book an Appointment</a>
      </div>
      <div class="hero__avatar">
        <img src="media/images/ruby_profile.jpg" alt="Dr. Ruby M. Suresh" />
      </div>
    </div>
  </header>

  <section class="about" id="about">
    <div class="container">
      <div class="about__image">
        <img src="media/images/ruby_about.jpg" alt="Dr. Ruby M. Suresh at Ephesians Dental" />
      </div>
      <div class="about__text">
        <h2>About Dr. Suresh</h2>
        <p>At Ephesians Dental, we believe every patient deserves exceptional care in a comfortable environment. Our experienced team uses the latest technology and techniques to provide comprehensive dental services, from routine cleanings and check-ups to advanced cosmetic and restorative treatments.</p>
        <p>With a commitment to gentle, compassionate dentistry, Dr. Ruby M. Suresh and her team have been serving families for over a decade, building lasting relationships based on trust and outstanding results.</p>
      </div>
    </div>
  </section>



  <section class="reviews" id="reviews">
    <div class="container">
      <h2>Client Reviews</h2>
      <?php if ($avg !== null): ?>
        <p class="reviews-summary">⭐ <?= h((string) $avg) ?> / 5 based on <?= h((string) $countRv) ?> review<?= $countRv === 1 ? '' : 's' ?></p>
      <?php endif; ?>

      <div class="reviews__grid">
        <?php foreach ($reviews as $r): ?>
          <div class="review-card" style="background:#ffffff;border:1px solid rgba(0,0,0,0.08);box-shadow:0 10px 30px rgba(0,0,0,0.06);">
            <div class="review-card__stars" style="color: #f5c518; letter-spacing: 2px; font-size: 1.2rem; margin-bottom: 12px;"><?= h(render_stars((int) $r['rating'])) ?></div>
            <h3 style="font-size: 1.2rem; margin-bottom: 4px;"><?= h($r['name']) ?></h3>
            <p style="font-size: 0.9rem; color: #888; margin-bottom: 16px;"><?= h(date('F j, Y', strtotime($r['created_at']))) ?></p>
            <p style="font-size: 1rem; color: #555;"><?= h($r['body']) ?></p>
          </div>
        <?php endforeach; ?>
        <?php if (count($reviews) === 0): ?>
          <p style="width:100%;text-align:center;color:#555;">Be the first to leave a review below.</p>
        <?php endif; ?>
      </div>
      
      <div class="review-card" style="max-width: 600px; margin: 60px auto 0; background:#ffffff; border:1px solid rgba(0,0,0,0.08); box-shadow:0 10px 30px rgba(0,0,0,0.06);">
        <h3 style="margin-bottom:24px;font-size:1.6rem;text-align:center;color:#9B1B30;">Write a Review</h3>
        <?php if ($reviewOk): ?>
          <div class="form-alert form-alert--ok">Thank you — your review was submitted.</div>
        <?php endif; ?>
        <?php if ($reviewErr): ?>
          <div class="form-alert form-alert--err">Please complete all review fields and choose a rating.</div>
        <?php endif; ?>
        <form method="post" action="actions/submit_review.php">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
          <div class="form-group">
            <label class="form-label" for="rev_name">Your Name</label>
            <input type="text" id="rev_name" name="name" required />
          </div>
          <div class="form-group">
            <label class="form-label" for="rev_rating">Rating</label>
            <select id="rev_rating" name="rating" required>
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>"><?= $i ?> star<?= $i === 1 ? '' : 's' ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="rev_body">Review</label>
            <textarea id="rev_body" name="body" rows="4" required></textarea>
          </div>
          <button type="submit" class="btn-primary" style="width:100%;">Submit Review</button>
        </form>
      </div>
    </div>
  </section>

  <section class="forms-section" id="appointment">
    <div class="container">
      <div class="forms-container">
        
        <div class="forms__left" id="contact">
          <h2>Book An Appointment</h2>

          <?php if ($bookOk): ?>
            <div class="form-alert form-alert--ok">We received your request. Ruby will confirm shortly.</div>
          <?php endif; ?>
          <?php if ($bookErr === 'missing_fields'): ?>
            <div class="form-alert form-alert--err">Please fill in all required booking fields.</div>
          <?php elseif ($bookErr === 'time_taken'): ?>
            <div class="form-alert form-alert--err">That time slot is already taken. Please pick another.</div>
          <?php endif; ?>

          <form method="post" action="actions/book_appointment.php" id="bookingForm">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />

            <div class="form-group">
              <label class="form-label" for="patient_name">Full Name</label>
              <input type="text" id="patient_name" name="patient_name" value="<?= h((string) ($bookOld['patient_name'] ?? '')) ?>" required />
            </div>

            <div class="form-row">
              <div>
                <label class="form-label" for="patient_email">Email</label>
                <input type="email" id="patient_email" name="patient_email" value="<?= h((string) ($bookOld['patient_email'] ?? '')) ?>" required />
              </div>
              <div>
                <label class="form-label" for="patient_phone">Phone Number</label>
                <input type="tel" id="patient_phone" name="patient_phone" value="<?= h((string) ($bookOld['patient_phone'] ?? '')) ?>" required />
              </div>
            </div>

            <div class="form-row">
              <div>
                <label class="form-label" for="appt_date">Date</label>
                <input type="date" id="appt_date" name="date" value="<?= h((string) ($bookOld['date'] ?? '')) ?>" required />
              </div>
              <div>
                <label class="form-label" for="time_start">Time</label>
                <select id="time_start" name="time_start" data-selected-time="<?= h((string) ($bookOld['time_start'] ?? '')) ?>" required disabled>
                  <option value="" disabled selected>Select a date first</option>
                  <?php foreach ($timeSlots as $val => $lab): ?>
                    <option value="<?= h($val) ?>"><?= h($lab) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="type">Appointment Type</label>
              <select id="type" name="type" required>
                <option value="" disabled selected>Select type</option>
                <?php foreach ($apptTypes as $t): ?>
                  <option value="<?= h($t) ?>"<?= (($bookOld['type'] ?? '') === $t) ? ' selected' : '' ?>><?= h($t) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="notes">Notes (optional)</label>
              <textarea id="notes" name="notes" rows="4" placeholder="Anything we should know?"><?= h((string) ($bookOld['notes'] ?? '')) ?></textarea>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;">Request Appointment</button>
          </form>
        </div>

        <div class="forms__right">
          <h2>Send A Message</h2>

          <?php if ($msgOk): ?>
            <div class="form-alert form-alert--ok">Your message was sent. Thank you!</div>
          <?php endif; ?>
          <?php if ($msgErr): ?>
            <div class="form-alert form-alert--err">Please enter your name, email, and message.</div>
          <?php endif; ?>

          <form method="post" action="actions/send_message.php">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />

            <div class="form-group">
              <label class="form-label" for="msg_name">Name</label>
              <input type="text" id="msg_name" name="name" required />
            </div>

            <div class="form-group">
              <label class="form-label" for="msg_email">Email</label>
              <input type="email" id="msg_email" name="email" required />
            </div>

            <div class="form-group">
              <label class="form-label" for="msg_subject">Subject (optional)</label>
              <input type="text" id="msg_subject" name="subject" />
            </div>

            <div class="form-group">
              <label class="form-label" for="msg_body">Message</label>
              <textarea id="msg_body" name="body" rows="6" required></textarea>
            </div>

            <button type="submit" class="btn-secondary" style="width:100%;">Send Message</button>
          </form>
        </div>

      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container">
      <div class="footer__hours footer__col">
        <h3>Hours of Operation</h3>
        <ul>
          <li>Monday: 10:00 AM - 6:00 PM</li>
          <li>Tuesday: 10:00 AM - 6:00 PM</li>
          <li>Wednesday: 10:00 AM - 6:00 PM</li>
          <li>Thursday: 10:00 AM - 6:00 PM</li>
          <li>Friday: 10:00 AM - 6:00 PM</li>
          <li>Saturday: 10:00 AM - 6:00 PM</li>
          <li>Sunday: 10:00 AM - 6:00 PM</li>
        </ul>
      </div>

      <div class="footer__middle">
        <div class="footer__col">
          <h3>Our Services</h3>
          <ul>
            <li>General Dentistry</li>
            <li>Cosmetic Dentistry</li>
            <li>Orthodontics</li>
            <li>Emergency Care</li>
          </ul>
        </div>
        <div class="footer__col">
          <h3>Contact</h3>
          <p>2065 Finch Ave W Unit 210<br/>North York, ON M3N 2V7</p>
          <p style="margin-top:16px;">(416) 743-6828</p>
          <p>ruby@clinic.com</p>
          <div class="footer__social">
            <a href="https://www.instagram.com/docrubydentistry" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
              <i class="fa-brands fa-instagram fa-2x"></i>
            </a>
            <a href="https://www.facebook.com/ruby.manlapaz.suresh.dental/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
              <i class="fa-brands fa-facebook fa-2x"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="footer__map">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2882.1643194090514!2d-79.5312384!3d43.7486801!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x882b304c6ca93f2f%3A0xe13c6b240cdcedb3!2s2065%20Finch%20Ave%20W%20Unit%20210%2C%20North%20York%2C%20ON%20M3N%202V7!5e0!3m2!1sen!2sca!4v1700000000000!5m2!1sen!2sca" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
    <div class="footer__bottom">
      <p>&copy; 2026 Dr. Ruby M. Suresh. All rights reserved.</p>
    </div>
  </footer>

  <script>
    window.clinicTimeSlots = <?php echo json_encode($timeSlots); ?>;
  </script>
  <script src="js/appointments.js"></script>
</body>
</html>

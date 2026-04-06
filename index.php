<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

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
$apptTypes = ['Check-up', 'Cleaning', 'Root Canal', 'Filling', 'Consultation', 'Whitening', 'Other'];

$errParam     = $_GET['error'] ?? '';
$successParam = $_GET['success'] ?? '';

$bookErr = in_array($errParam, ['missing_fields', 'time_taken'], true) ? $errParam : '';
$bookOk  = ($successParam === '1');

$msgErr = ($errParam === 'msg_missing');
$msgOk  = ($successParam === 'msg');

$reviewErr = ($errParam === 'review');
$reviewOk  = ($successParam === 'review');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ruby Suresh Dental Clinic</title>
  <meta name="description" content="Ruby Suresh Dental Clinic — Your Smile, Our Priority." />
  <link rel="stylesheet" href="css/styles.css" />
  <style>
    .form-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 0.95rem; }
    .form-alert--ok { background: rgba(52,168,83,0.15); color: #1e7e34; }
    .form-alert--err { background: #fdecea; color: #9B1B30; }
    .reviews-summary { text-align: center; margin: -24px 0 32px; color: #555; font-size: 1.05rem; }
  </style>
</head>
<body>

  <nav class="navbar" id="navbar">
    <div class="navbar__logo">Ruby Suresh Dental</div>
    <ul class="navbar__links">
      <li><a href="#hero">Home</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#services">Services</a></li>
      <li><a href="#contact">Contact</a></li>
      <li><a href="admin.php">Admin</a></li>
    </ul>
    <div class="navbar__cta">
      <a href="#appointment" class="btn-primary">Book an Appointment</a>
    </div>
  </nav>

  <header class="hero" id="hero">
    <div class="hero__content">
      <h1>Your Smile, Our Priority</h1>
      <p>Welcome to Ruby Suresh Dental Clinic — where advanced dental care meets a warm, patient-first approach. We're here to help you achieve the healthy, confident smile you deserve.</p>
      <a href="#appointment" class="btn-primary">Book an Appointment</a>
    </div>
    <div class="hero__avatar" aria-label="Doctor portrait placeholder"></div>
  </header>

  <section class="about" id="about">
    <div class="about__image">
      <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <polygon points="15,85 40,30 65,85" fill="#b0b0b0"/>
        <polygon points="45,85 70,40 95,85" fill="#999"/>
        <circle cx="78" cy="22" r="8" fill="#ccc"/>
      </svg>
    </div>
    <div class="about__text">
      <h2>About</h2>
      <p>At Ruby Suresh Dental Clinic, we believe every patient deserves exceptional care in a comfortable environment. Our experienced team uses the latest technology and techniques to provide comprehensive dental services — from routine cleanings and check-ups to advanced cosmetic and restorative treatments.</p>
      <p style="margin-top:12px;">With a commitment to gentle, compassionate dentistry, Dr. Ruby Suresh and her team have been serving families for over a decade, building lasting relationships based on trust and outstanding results.</p>
    </div>
  </section>

  <section class="reviews" id="services">
    <h2>Client Reviews</h2>
    <?php if ($avg !== null): ?>
      <p class="reviews-summary">⭐ <?= h((string) $avg) ?> / 5 based on <?= h((string) $countRv) ?> review<?= $countRv === 1 ? '' : 's' ?></p>
    <?php endif; ?>

    <div class="reviews__grid">
      <?php foreach ($reviews as $r): ?>
        <div class="review-card">
          <div class="review-card__header">
            <div class="review-card__header-left">
              <div class="review-card__avatar"></div>
              <span class="review-card__name"><?= h($r['name']) ?></span>
            </div>
            <span class="review-card__google"><span class="g-blue">G</span></span>
          </div>
          <div class="review-card__stars"><?= h(render_stars((int) $r['rating'])) ?></div>
          <p class="review-card__text"><?= h($r['body']) ?></p>
          <p class="review-card__text" style="margin-top:8px;font-size:0.8rem;opacity:0.75;"><?= h(date('F j, Y', strtotime($r['created_at']))) ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (count($reviews) === 0): ?>
        <p style="width:100%;text-align:center;color:#555;">Be the first to leave a review below.</p>
      <?php endif; ?>
    </div>

    <div class="review-card" style="max-width:480px;margin:40px auto 0;text-align:left;">
      <h3 style="color:#9B1B30;margin-bottom:12px;font-size:1.2rem;">Write a Review</h3>
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
  </section>

  <section class="forms" id="appointment">

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

      <form method="post" action="actions/book_appointment.php">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />

        <div class="form-group">
          <label class="form-label" for="patient_name">Full Name</label>
          <input type="text" id="patient_name" name="patient_name" required />
        </div>

        <div class="form-row">
          <div>
            <label class="form-label" for="patient_email">Email</label>
            <input type="email" id="patient_email" name="patient_email" required />
          </div>
          <div>
            <label class="form-label" for="patient_phone">Phone (optional)</label>
            <input type="tel" id="patient_phone" name="patient_phone" />
          </div>
        </div>

        <div class="form-row">
          <div>
            <label class="form-label" for="appt_date">Date</label>
            <input type="date" id="appt_date" name="date" required />
          </div>
          <div>
            <label class="form-label" for="time_start">Time</label>
            <select id="time_start" name="time_start" required>
              <option value="" disabled selected>Select a time</option>
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
              <option value="<?= h($t) ?>"><?= h($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="notes">Notes (optional)</label>
          <textarea id="notes" name="notes" rows="4" placeholder="Anything we should know?"></textarea>
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

        <div class="btn-wrap">
          <button type="submit" class="btn-secondary">Send</button>
        </div>
      </form>
    </div>

  </section>

  <footer class="footer">
    <div class="footer__columns">
      <div class="footer__col">
        <h3>Our Services</h3>
        <ul>
          <li>General Dentistry</li>
          <li>Cosmetic Dentistry</li>
          <li>Orthodontics</li>
        </ul>
      </div>
      <div class="footer__col">
        <h3>Hours of Operation</h3>
        <ul>
          <li>Monday: 9:00 AM – 6:00 PM</li>
          <li>Tuesday: 9:00 AM – 6:00 PM</li>
          <li>Wednesday: 9:00 AM – 6:00 PM</li>
          <li>Thursday: 9:00 AM – 6:00 PM</li>
          <li>Friday: 9:00 AM – 5:00 PM</li>
          <li>Saturday: 10:00 AM – 3:00 PM</li>
          <li>Sunday: Closed</li>
        </ul>
      </div>
      <div class="footer__col">
        <h3>Contact</h3>
        <p>123 Ruby Lane, Suite 100<br/>Toronto, ON M5V 2T6</p>
        <p style="margin-top:8px;">(416) 555-0198</p>
        <p>info@rubysuresh.dental</p>
      </div>
    </div>
    <hr class="footer__divider" />
    <p class="footer__copy">&copy; 2026 Ruby Suresh Dental Clinic. All rights reserved.</p>
  </footer>

</body>
</html>

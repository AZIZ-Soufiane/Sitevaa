<?php
error_reporting(E_ALL);
require_once('../Team/config/db.php');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $budget = floatval($_POST['budget'] ?? 0);
    $timeline = trim($_POST['timeline'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    }
    if(empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    }
    if (empty($description)) {
        $errors['description'] = 'Project description is required';
    }


    if (empty($errors)) {
        try {
            // Check if client exists
            $checkStmt = $pdo->prepare("SELECT Client_Id FROM Client WHERE email = ?");
            $checkStmt->execute([$email]);
            $existingClient = $checkStmt->fetch();

            if ($existingClient) {
                $clientId = $existingClient['Client_Id'];
            } else {
                // Insert new client
                $clientStmt = $pdo->prepare("INSERT INTO Client (name, email, phone) VALUES (?, ?, ?)");
                $clientStmt->execute([$name, $email, $phone]);
                $clientId = $pdo->lastInsertId();
            }

            // Insert project request
            $requestStmt = $pdo->prepare("INSERT INTO Project_request (description, budget, timeline, status, Client_id) VALUES (?, ?, ?, 'pending', ?)");
            $requestStmt->execute([$description, $budget, $timeline, $clientId]);
            
            $success = true;
            // Redirect after successful submission
            header('Location: home.php?success=1#contact');
            exit();

        } catch(PDOException $e) {
            $errors['general'] = 'An error occurred. Please try again.';
        }
    }
}

// Show success message if redirected after submission
if (isset($_GET['success'])) {
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="home.css">
    <title>SITEVAA - Creative Digital Agency</title>
</head>

<body>
    <div class="container">
        <nav class="navbar">
            <div class="nav-content">
                <div class="logo">
                    <a href="#"><img src="./images/logonavbar.png" alt="SITEVAA Logo"></a>
                </div>
                <ul class="nav-links">
                    <li><a href="#services">Services</a></li>
                    <li><a href="#works">Works</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <button class="nav-cta" onclick="window.location.href='#contact'">Let's talk!</button>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>

        <section class="hero">
            <div class="hero-content">
                <h3 class="hero-title">WE ARE</h3>
                <h1 class="hero-brand">SITEVAA</h1>
                <p class="hero-description">
                    We turn your ideas into reality with creativity and precision. Let's work together to bring your
                    vision to life.
                </p>
                <a href="#contact" class="start-project-btn">Start A Project</a>
            </div>
            <div class="hero-visual">
                <img src="./images/Full_trans 3.png" alt="SITEVAA Hero Image" class="hero-img">
            </div>
        </section>
        <section class="services-wrapper" id="services">
            <div class="services-section">
                <div class="services-header">
                    <div class="services-tag">WHAT WE DO</div>
                    <div class="services-title-container">
                        <h2 class="services-title">
                            We transform your ideas into unique<br>
                            web projects that inspire both you<br>
                            and your customers.
                        </h2>
                        <span class="highlight">SERVICES</span>
                    </div>
                </div>

                <div class="services-grid">
                    <div class="service-card">
                        <div class="service-icon">
                            <img src="./images/iMac Settings.png" alt="">
                        </div>
                        <h3 class="service-title">Custom Websites</h3>
                        <p class="service-description">
                            Create websites tailored to fit your exact needs and vision.
                        </p>
                    </div>

                    <div class="service-card">
                        <div class="service-icon">
                            <img src="./images/Online Store.png" alt="">
                        </div>
                        <h3 class="service-title">Ecommerce Websites</h3>
                        <p class="service-description">
                            Build custom online stores designed to help you sell and grow your business.
                        </p>
                    </div>

                    <div class="service-card">
                        <div class="service-icon">
                            <img src="./images/Windows 10 Personalization.png" alt="">
                        </div>
                        <h3 class="service-title">UX/UI Design</h3>
                        <p class="service-description">
                            Create user-friendly designs that make your website easy to navigate and engaging.
                        </p>
                    </div>

                    <div class="service-card">
                        <div class="service-icon">
                            <img src="./images/Security Portal.png" alt="">
                        </div>
                        <h3 class="service-title">Security & Hosting</h3>
                        <p class="service-description">
                            Set up reliable hosting with strong security measures to ensure your website runs smoothly
                            and
                            stays protected.
                        </p>
                    </div>

                    <div class="service-card">
                        <div class="service-icon">
                            <img src="./images/Web Advertising.png" alt="">
                        </div>
                        <h3 class="service-title">SEO & Marketing</h3>
                        <p class="service-description">
                            Optimize your website for search engines and help you reach a wider audience through
                            effective
                            digital marketing strategies.
                        </p>
                    </div>

                    <div class="service-card">
                        <div class="service-icon">
                            <img src="./images/Processor.png" alt="">
                        </div>
                        <h3 class="service-title">AI Bots Integration</h3>
                        <p class="service-description">
                            Develop AI-powered bots to automate customer support, lead generation, and enhance user
                            interaction.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="works-wrapper" id="works">
            <div class="works-section">
                <h1 class="section-title">Recent Works</h1>

                <div class="filter-nav">
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="apps">Apps</button>
                        <button class="filter-btn" data-filter="branding">Branding</button>
                        <button class="filter-btn" data-filter="ux-ui">UX/UI</button>
                    </div>
                </div>

                <div class="works-grid">
                    <div class="work-card" data-category="apps">
                        <div class="work-image">
                            <img src="images/starsky.png" alt="">
                        </div>
                        <div class="work-content">
                            <h3 class="work-title">STARSKY.APP</h3>
                            <p class="work-description">Starsky.app turns ideas into web apps instantly—just by
                                describing
                                them
                                in plain English. No code, no developers—just pure speed.</p>
                        </div>
                        <a href="https://starsky.app" target="_blank" class="work-arrow">
                            <img src="./images/arrow.svg" alt="Arrow">
                        </a>
                    </div>

                    <div class="work-card" data-category="apps">
                        <div class="work-image">
                            <img src="images/monzed.png" alt="">
                        </div>
                        <div class="work-content">
                            <h3 class="work-title">MONZED.COM</h3>
                            <p class="work-description">Your AI-Powered Development Agency, turning innovative ideas
                                into reality with cutting-edge AI solutions, professional development, and design.</p>
                        </div>
                        <a href="https://monzed.com" target="_blank" class="work-arrow">
                            <img src="./images/arrow.svg" alt="Arrow">
                        </a>
                    </div>

                    <div class="work-card" data-category="ux-ui branding">
                        <div class="work-image">
                            <img src="images/booknestUIUX.png" alt="">
                        </div>
                        <div class="work-content">
                            <h3 class="work-title">UTOPIA-BOOKNEST DESIGN</h3>
                            <p class="work-description">A sleek, user-friendly design made for easy book discovery and
                                checkout.
                                Optimized for smooth browsing and a seamless shopping experience.</p>
                        </div>
                        <a href="https://www.figma.com/design/VNznSVZyCXxNmbbRBCWRm8/Utopia-BooksNest?node-id=1-3&t=wfpstBte0Jtllade-1"
                            target="_blank" class="work-arrow">
                            <img src="./images/arrow.svg" alt="Arrow">
                        </a>
                    </div>

                    <div class="work-card" data-category="branding apps">
                        <div class="work-image">
                            <img src="images/mokashop.png" alt="">
                        </div>
                        <div class="work-content">
                            <h3 class="work-title">MOKASHOP.CC</h3>
                            <p class="work-description">Mokashop.cc is an online store offering a wide range of digital
                                goods at
                                affordable prices—fast, easy, and accessible for everyone.</p>
                        </div>
                        <a href="https://Mokashop.cc" target="_blank" class="work-arrow">
                            <img src="./images/arrow.svg" alt="Arrow">
                        </a>
                    </div>
                </div>
            </div>
        </section>

        </section>

        <section class="feedback-wrapper" id="testimonials">
            <div class="feedback-section">
                <header class="feedback-header">
                    <h1>FEEDBACK</h1>
                    <p>Real feedback. Real results. Hear it from our clients.</p>
                </header>

                <div class="testimonials-container">
                    <div class="testimonials-column">
                        <div class="testimonial-card">
                            <h3>Baalla Ayoub</h3>
                            <p class="title">Founder of Starsky.app</p>
                            <blockquote>"Working with Sitevaa was a smooth and professional experience. They quickly
                                understood our goals and delivered a clean, responsive website that perfectly matched
                                our brand. Communication was clear, deadlines were met, and their support after launch
                                was excellent. Highly recommended for anyone seeking quality web development services."
                            </blockquote>
                        </div>
                        <div class="testimonial-card">
                            <h3>Ben.Anouar</h3>
                            <p class="title">Founder of UtopiaBookNest</p>
                            <blockquote>"The design was clean, modern, and perfectly captured our brand — truly
                                impressive work by Sitevaa."</blockquote>
                        </div>
                    </div>

                    <div class="testimonials-column">
                        <div class="testimonial-card">
                            <h3>Aziz Soufiane</h3>
                            <p class="title">CEO of Monzed.com</p>
                            <blockquote>"We had a great experience with Sitevaa. Their team was efficient, creative, and
                                detail-oriented. They delivered a high-quality website on time and provided solid
                                support throughout. We're very satisfied with the results."</blockquote>
                        </div>
                        <div class="testimonial-card">
                            <h3>Baazi Morad</h3>
                            <p class="title">Owner of Mokashop.cc</p>
                            <blockquote>"Excellent service from start to finish. Sitevaa took our ideas and turned them
                                into a functional, modern website. Their communication was timely, and the final product
                                exceeded our expectations. Would gladly work with them again."</blockquote>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact-wrapper" id="contact">
            <div class="contact-container">
                <div class="form-section">
                    <h1 class="contact-title">CONTACT US</h1>
                    <p class="quote">"Every great achievement begins as a simple idea."</p>

                    <?php if ($success): ?>
                        <div class="success-message">
                            Thank you! Your request has been submitted successfully.
                        </div>
                    <?php endif; ?>

                    <?php if (isset($errors['general'])): ?>
                        <div class="error-message general-error">
                            <?php echo $errors['general']; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="home.php#contact" id="contactForm">
                        <div class="form-group">
                            <input type="text" 
                                   name="name" 
                                   class="form-input <?php echo isset($errors['name']) ? 'error' : ''; ?>" 
                                   placeholder="Name" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            <?php if (isset($errors['name'])): ?>
                                <div class="error-message"><?php echo $errors['name']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <input type="email" 
                                   name="email" 
                                   class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>" 
                                   placeholder="Email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="error-message"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <input type="tel" 
                                   name="phone" 
                                   class="form-input" 
                                   placeholder="Phone" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            <?php if (isset($errors['phone'])): ?>
                                <div class="error-message"><?php echo $errors['phone']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <select name="budget" class="form-select">
                                <option value="" disabled <?php echo !isset($_POST['budget']) ? 'selected' : ''; ?>>Budget(Optional)</option>
                                <option value="5000" <?php echo isset($_POST['budget']) && $_POST['budget'] == '5000' ? 'selected' : ''; ?>>Less than 5k</option>
                                <option value="15000" <?php echo isset($_POST['budget']) && $_POST['budget'] == '15000' ? 'selected' : ''; ?>>5k to 15k</option>
                                <option value="30000" <?php echo isset($_POST['budget']) && $_POST['budget'] == '30000' ? 'selected' : ''; ?>>15k to 30k</option>
                                <option value="50000" <?php echo isset($_POST['budget']) && $_POST['budget'] == '50000' ? 'selected' : ''; ?>>30k and more</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <input type="date" 
                                   name="timeline" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['timeline'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <textarea name="description" 
                                      class="form-textarea <?php echo isset($errors['description']) ? 'error' : ''; ?>" 
                                      placeholder="Project Description..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="error-message"><?php echo $errors['description']; ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="submit-btn">Submit Request</button>
                    </form>
                </div>

                <div class="contact-info">
                    <h2 class="hate-forms">Hate contact forms ?</h2>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                        </div>
                        <span>hello@monzed.com</span>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                        </div>
                        <span>+212 633895448</span>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                        <span>Clermont-Ferrand, France</span>
                    </div>
                </div>
            </div>
        </section>


        </section>

        <footer class="site-footer">
            <div class="footer-container">
                <div class="footer-top-section">
                    <div class="footer-info">
                        <a href="#" class="logo-link">
                            <img src="images/logo.png" alt="SITEVAA Logo">
                        </a>
                        <p class="tagline">
                            We turn your ideas into reality with creativity and precision. Let's work together to bring
                            your vision to life.
                        </p>
                    </div>

                    <div class="social-media">
                        <a href="#" aria-label="LinkedIn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z">
                                </path>
                                <rect x="2" y="9" width="4" height="12"></rect>
                                <circle cx="4" cy="4" r="2"></circle>
                            </svg>
                        </a>
                        <a href="#" aria-label="X">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z" />
                            </svg>
                        </a>
                        <a href="#" aria-label="Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="footer-bottom-section">
                    <p>© 2025 Sitevaa . All rights reserved</p>
                </div>
            </div>
        </footer>

    </div>
    <script src="script.js"></script>
</body>

</html>
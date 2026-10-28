<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/config/db.php';

$profile = get_profile_data($pdo, $db_connected);
$projects = get_projects($pdo, $db_connected);
$certificates = get_certificates($pdo, $db_connected, true);
$resume_info = get_resume_info($pdo, $db_connected);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kian Lee Raian M. Aguinaldo - Bachelor of Science in Information Technology Graduate. Portfolio showcasing projects, skills, and experience.">
    <title>Kian Lee Raian M. Aguinaldo | IT Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime('assets/css/style.css'); ?>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="#home"><span class="logo-text">Kian</span><span class="logo-dot">.</span></a>
            </div>
            <div class="nav-menu" id="navMenu">
                <a href="#home" class="nav-link">Home</a>
                <a href="#about" class="nav-link">About</a>
                <a href="#skills" class="nav-link">Skills</a>
                <a href="#projects" class="nav-link">Projects</a>
                <a href="#certificates" class="nav-link">Certificates</a>
                <a href="#experience" class="nav-link">Experience</a>
                <a href="#contact" class="nav-link">Contact</a>
            </div>
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- Particle Canvas Background -->
    <canvas id="particleCanvas" aria-hidden="true"></canvas>

    <!-- =================== HERO SECTION =================== -->
    <section id="home" class="hero-section">
        <div class="hero-content">
            <div class="hero-badge animate-fade-up" style="--delay: 0.1s">&#128187; IT Graduate</div>
            <h1 class="hero-title animate-fade-up" style="--delay: 0.2s">
                Hi, I'm <span class="gradient-text">Kian</span>
            </h1>
            <h2 class="hero-subtitle animate-fade-up" style="--delay: 0.3s">
                Kian Lee Raian M. Aguinaldo
            </h2>
            <p class="hero-desc animate-fade-up" style="--delay: 0.4s">
                An aspiring IT professional passionate about technology, problem-solving, and building practical digital solutions. Eager to learn, grow, and contribute to meaningful projects through technical skills and continuous improvement.
            </p>
            <div class="hero-buttons animate-fade-up" style="--delay: 0.5s">
                <a href="#projects" class="btn btn-primary">View My Projects</a>
                <?php if ($resume_info['exists']): ?>
                    <a href="download-resume.php" class="btn btn-resume" download="Kian_Aguinaldo_Resume.pdf" title="Download Resume (PDF)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        <span>Download Resume</span>
                    </a>
                <?php endif; ?>
                <a href="#contact" class="btn btn-outline">Get In Touch</a>
            </div>
            <div class="hero-scroll-indicator animate-fade-up" style="--delay: 0.7s">
                <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
                <span>Scroll Down</span>
            </div>
        </div>
    </section>

    <!-- =================== ABOUT SECTION =================== -->
    <section id="about" class="section about-section">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">About Me</span>
                <h2 class="section-title">Who Am I?</h2>
            </div>
            <div class="about-grid">
                <div class="about-image-col reveal">
                    <div class="about-image-wrapper">
                        <div class="about-image-placeholder">
                            <img src="assets/img/profile.jpg" alt="Kian Lee Raian M. Aguinaldo" class="about-image">
                        </div>
                        <div class="about-image-glow"></div>
                    </div>
                </div>
                <div class="about-text-col">
                    <div class="reveal">
                        <h3 class="about-heading"><?= htmlspecialchars($profile['title']) ?></h3>
                        <p class="about-text">
                            I'm an Information Technology graduate interested in IT support, troubleshooting, and practical technology solutions. Through my internship and personal projects, I've gained exposure to computer setup, software installation, basic networking, and web technologies. I'm eager to continue learning, strengthen my technical skills, and contribute to a professional IT team.
                        </p>
                        <p class="about-text">
                            I focus on maintaining dependable systems, assisting users with day-to-day technical challenges, and continuously expanding my practical knowledge in hardware, software, and network environments.
                        </p>
                    </div>
                    <div class="about-stats reveal">
                        <div class="stat-item">
                            <span class="stat-number">BSIT</span>
                            <span class="stat-label">Degree</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">STI</span>
                            <span class="stat-label">College Lucena</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">2022&ndash;2026</span>
                            <span class="stat-label">Academic Years</span>
                        </div>
                    </div>
                    <div class="about-contact-info reveal">
                        <a href="tel:09762022723" class="contact-info-item">
                            <span class="contact-icon">&#128222;</span>
                            <span>09762022723</span>
                        </a>
                        <a href="mailto:kianaguinaldo7@gmail.com" class="contact-info-item">
                            <span class="contact-icon">&#128231;</span>
                            <span>kianaguinaldo7@gmail.com</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =================== SKILLS SECTION =================== -->
    <section id="skills" class="section skills-section">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">Skills</span>
                <h2 class="section-title">Technical Expertise</h2>
                <p class="section-subtitle">Core technical capabilities, support proficiencies, and developer tools</p>
            </div>
            <div class="skills-wrapper">

                <!-- IT Support -->
                <div class="skills-category-block reveal" style="--stagger: 0.05s">
                    <div class="category-heading">
                        <span class="category-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </span>
                        <h3 class="category-title">IT Support</h3>
                    </div>
                    <div class="skill-cards-grid">
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="14" height="10" rx="2"/><line x1="6" y1="17" x2="12" y2="17"/><line x1="9" y1="13" x2="9" y2="17"/><rect x="18" y="5" width="4" height="14" rx="1"/><line x1="20" y1="8" x2="20.01" y2="8"/><line x1="20" y1="11" x2="20.01" y2="11"/></svg>
                            </span>
                            <h4 class="skill-card-name">PC Setup &amp; Configuration</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="7 10 12 13 17 10"/><line x1="12" y1="13" x2="12" y2="6"/></svg>
                            </span>
                            <h4 class="skill-card-name">Software Installation</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                            </span>
                            <h4 class="skill-card-name">Basic Troubleshooting</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 24 24"><path fill="#F25022" d="M1 1h10v10H1z"/><path fill="#7FBA00" d="M13 1h10v10H13z"/><path fill="#00A4EF" d="M1 13h10v10H1z"/><path fill="#FFB900" d="M13 13h10v10H13z"/></svg>
                            </span>
                            <h4 class="skill-card-name">Microsoft Office</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="2" width="6" height="5" rx="1"/><rect x="2" y="17" width="6" height="5" rx="1"/><rect x="16" y="17" width="6" height="5" rx="1"/><path d="M12 7v5"/><path d="M5 17v-2a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v2"/></svg>
                            </span>
                            <h4 class="skill-card-name">Basic Networking</h4>
                        </div>
                    </div>
                </div>

                <!-- Web Development & Databases -->
                <div class="skills-category-block reveal" style="--stagger: 0.15s">
                    <div class="category-heading">
                        <span class="category-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                        </span>
                        <h3 class="category-title">Web Development &amp; Databases</h3>
                    </div>
                    <div class="skill-cards-grid">
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 128 128"><path fill="#E44D26" d="M19.037 113.876L9.032 1.661h109.936l-10.016 112.198-45.019 12.48z"/><path fill="#F16529" d="M64 116.8l36.378-10.086 8.559-95.878H64z"/><path fill="#EBEBEB" d="M64 52.455H45.788L44.53 38.361H64V24.599H29.489l.33 3.692 3.382 37.927H64zm0 35.743l-.061.017-15.327-4.14-.979-10.975H33.816l1.928 21.609 28.193 7.826.063-.017z"/><path fill="#fff" d="M63.952 52.455v13.763h16.947l-1.597 17.849-15.35 4.143v14.319l28.215-7.82.207-2.325 3.234-36.233.335-3.696h-3.708zm0-27.856v13.762h33.244l.276-3.092.628-6.978.329-3.692z"/></svg>
                            </span>
                            <h4 class="skill-card-name">HTML5</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 128 128"><path fill="#1572B6" d="M18.814 114.123L8.76 1.352h110.48l-10.064 112.754-45.243 12.543-45.119-12.526z"/><path fill="#33A9DC" d="M64.001 117.062l36.559-10.136 8.601-96.354h-45.16v106.49z"/><path fill="#fff" d="M64.001 51.429h18.302l1.264-14.163H64.001V23.435h34.682l-.332 3.711-3.4 38.114h-30.95V51.429z"/><path fill="#EBEBEB" d="M64.083 87.349l-.061.018-15.403-4.159-.985-11.031H33.752l1.937 21.717 28.331 7.863.063-.018v-14.39z"/><path fill="#fff" d="M81.127 64.675l-1.666 18.522-15.426 4.164v14.39l28.354-7.858.208-2.337 2.406-26.881H81.127z"/><path fill="#EBEBEB" d="M64.048 23.435v13.831H30.64l-.277-3.108-.63-7.012-.331-3.711h34.646zm-.047 27.996v13.831H48.792l-.277-3.108-.631-7.012-.33-3.711h16.447z"/></svg>
                            </span>
                            <h4 class="skill-card-name">CSS3</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 128 128"><path fill="#F0DB4F" d="M1.408 1.408h125.184v125.185H1.408z"/><path fill="#323330" d="M116.347 96.736c-.917-5.711-4.641-10.508-15.672-14.981-3.832-1.761-8.104-3.022-9.377-5.926-.452-1.69-.512-2.642-.226-3.665.821-3.32 4.784-4.355 7.925-3.403 2.023.678 3.938 2.237 5.093 4.724 5.402-3.498 5.391-3.475 9.163-5.879-1.381-2.141-2.118-3.129-3.022-4.045-3.249-3.629-7.676-5.498-14.756-5.355l-3.688.477c-3.534.893-6.902 2.748-8.877 5.235-5.926 6.724-4.236 18.492 2.975 23.335 7.104 5.332 17.54 6.545 18.873 11.531 1.297 6.104-4.486 8.08-10.234 7.378-4.236-.881-6.592-3.034-9.139-6.949-4.688 2.713-4.688 2.713-9.508 5.485 1.143 2.499 2.344 3.63 4.26 5.795 9.068 9.198 31.76 8.746 35.83-5.176.165-.478 1.261-3.666.38-8.581zM69.462 58.943H57.753l-.048 30.272c0 6.438.333 12.34-.714 14.149-1.713 3.558-6.152 3.117-8.175 2.427-2.059-1.012-3.106-2.451-4.319-4.485-.333-.584-.583-1.036-.667-1.071l-9.52 5.83c1.583 3.249 3.915 6.069 6.902 7.901 4.462 2.678 10.459 3.499 16.731 2.059 4.082-1.189 7.604-3.652 9.448-7.401 2.666-4.915 2.094-10.864 2.07-17.444.06-10.735.001-21.468.001-32.237z"/></svg>
                            </span>
                            <h4 class="skill-card-name">JavaScript</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 128 128"><ellipse cx="64" cy="64" rx="61.5" ry="31.2" fill="#777BB3"/><path fill="#FFFFFF" d="M34.77 67.86c2.79 0 4.88-.51 6.2-1.53 1.3-1.01 2.2-2.75 2.68-5.18.44-2.27.27-3.85-.5-4.71-.79-.87-2.5-1.32-5.07-1.32h-4.46l-2.47 12.74zm-14.6 15.68l6.56-33.74h14.13c4.45 0 7.75 1.21 9.83 3.59 2.09 2.39 2.73 5.73 1.92 9.93-.34 1.71-.9 3.3-1.7 4.73a15.8 15.8 0 0 1-3.13 3.92c-1.53 1.43-3.26 2.47-5.15 3.08-1.85.6-4.23.91-7.07.91h-5.72l-1.64 8.41h-8.03zm49.29-8.97l2.9-14.93c.28-1.42.21-2.44-.19-2.87-.24-.26-.98-.7-3.15-.7h-5.26l-3.64 18.77h-7.29l6.56-33.74h7.29l-1.88 9.68h5.65c4.31 0 7.23.76 8.93 2.32 1.73 1.6 2.28 4.14 1.61 7.57l-3.05 15.7h-8.48zm22.68-6.71c2.79 0 4.88-.51 6.2-1.53 1.3-1.01 2.2-2.75 2.68-5.18.44-2.27.27-3.85-.5-4.71-.79-.87-2.5-1.32-5.07-1.32h-4.46l-2.48 12.74zm-14.6 15.68l6.56-33.74h14.13c4.45 0 7.75 1.21 9.83 3.59 2.09 2.39 2.73 5.73 1.92 9.93a15.9 15.9 0 0 1-1.7 4.73 15.8 15.8 0 0 1-3.13 3.92c-1.53 1.43-3.26 2.47-5.15 3.08-1.85.6-4.23.91-7.07.91h-5.72l-1.64 8.41h-8.03z"/></svg>
                            </span>
                            <h4 class="skill-card-name">PHP</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 128 128"><path fill="#00618A" d="M117.688 98.242c-6.973-.191-12.297.461-16.852 2.379-1.293.547-3.355.559-3.566 2.18.711.746.82 1.859 1.387 2.777 1.086 1.754 2.922 4.113 4.559 5.352 1.789 1.348 3.633 2.793 5.551 3.961 3.414 2.082 7.223 3.27 10.504 5.352 1.938 1.23 3.859 2.777 5.75 4.164.934.684 1.563 1.75 2.773 2.18v-.195c-.637-.812-.801-1.93-1.387-2.777l-2.578-2.578c-2.52-3.344-5.719-6.281-9.117-8.719-2.711-1.949-8.781-4.578-9.91-7.73l-.199-.199c1.922-.219 4.172-.914 5.949-1.391 2.98-.797 5.645-.59 8.719-1.387l4.164-1.187v-.793c-1.555-1.594-2.664-3.707-4.359-5.152-4.441-3.781-9.285-7.555-14.273-10.703-2.766-1.746-6.184-2.883-9.117-4.363-.988-.496-2.719-.758-3.371-1.586-1.539-1.961-2.379-4.449-3.566-6.738-2.488-4.793-4.93-10.023-7.137-15.066-1.504-3.437-2.484-6.828-4.359-9.91-9-14.797-18.687-23.73-33.695-32.508-3.195-1.867-7.039-2.605-11.102-3.57l-6.543-.395c-1.332-.555-2.715-2.184-3.965-2.977C16.977 3.52 4.223-3.312.539 5.672-1.785 11.34 4.016 16.871 6.09 19.746c1.457 2.012 3.32 4.273 4.359 6.539.688 1.492.805 2.984 1.391 4.559 1.438 3.883 2.695 8.109 4.559 11.695.941 1.816 1.98 3.727 3.172 5.352.727.996 1.98 1.438 2.18 2.973-1.227 1.715-1.297 4.375-1.984 6.543-3.098 9.77-1.926 21.91 2.578 29.137 1.383 2.223 4.641 6.98 9.117 5.156 3.918-1.598 3.043-6.539 4.164-10.902.254-.988.098-1.715.594-2.379v.199l3.57 7.133c2.641 4.254 7.324 8.699 11.297 11.699 2.059 1.555 3.68 4.242 6.344 5.152v-.199h-.199c-.516-.805-1.324-1.137-1.98-1.781-1.551-1.523-3.277-3.414-4.559-5.156-3.613-4.902-6.805-10.27-9.711-15.855-1.391-2.668-2.598-5.609-3.77-8.324-.453-1.047-.445-2.633-1.387-3.172-1.281 1.988-3.172 3.598-4.164 5.945-1.582 3.754-1.789 8.336-2.375 13.082-.348.125-.195.039-.398.199-2.762-.668-3.73-3.508-4.758-5.949-2.594-6.164-3.078-16.09-.793-23.191.59-1.836 3.262-7.617 2.18-9.316-.516-1.691-2.219-2.672-3.172-3.965-1.18-1.598-2.355-3.703-3.172-5.551-2.125-4.805-3.113-10.203-5.352-15.062-1.07-2.324-2.875-4.676-4.359-6.738-1.645-2.289-3.484-3.977-4.758-6.742-.453-.984-1.066-2.559-.398-3.566.215-.684.516-.969 1.191-1.191 1.148-.887 4.352.297 5.547.793 3.18 1.32 5.832 2.578 8.527 4.363 1.289.855 2.598 2.512 4.16 2.973h1.785c2.789.641 5.914.195 8.523.988 4.609 1.402 8.738 3.582 12.488 5.949 11.422 7.215 20.766 17.48 27.156 29.734 1.027 1.973 1.473 3.852 2.379 5.945 1.824 4.219 4.125 8.559 5.941 12.688 1.816 4.113 3.582 8.27 6.148 11.695 1.348 1.801 6.551 2.766 8.918 3.766 1.66.699 4.379 1.43 5.949 2.379 3 1.809 5.906 3.965 8.723 5.945 1.402.992 5.73 3.168 5.945 4.957zm-88.605-75.52c-1.453-.027-2.48.156-3.566.395v.199h.195c.695 1.422 1.918 2.34 2.777 3.566l1.98 4.164.199-.195c1.227-.867 1.789-2.25 1.781-4.363-.492-.52-.562-1.164-.992-1.785-.562-.824-1.66-1.289-2.375-1.98z"/></svg>
                            </span>
                            <h4 class="skill-card-name">MySQL</h4>
                        </div>
                    </div>
                </div>

                <!-- Tools -->
                <div class="skills-category-block reveal" style="--stagger: 0.25s">
                    <div class="category-heading">
                        <span class="category-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        </span>
                        <h3 class="category-title">Tools</h3>
                    </div>
                    <div class="skill-cards-grid">
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 24 24" fill="#FB7A24"><path d="M16.792,11.923c0.113,0.043,0.226,0.079,0.334,0.128c0.45,0.203,0.715,0.553,0.748,1.044 c0.041,0.634,0.044,1.271,0.002,1.905c-0.049,0.732-0.725,1.292-1.483,1.271c-0.735-0.021-1.369-0.62-1.397-1.341 c-0.017-0.441-0.003-0.884-0.006-1.326c-0.001-0.239-0.003-0.242-0.245-0.243c-1.363-0.001-2.726,0.008-4.089-0.003 c-0.888-0.007-1.421,0.482-1.471,1.46c-0.019,0.38-0.1,0.727-0.357,1.018c-0.397,0.451-0.898,0.601-1.472,0.466 c-0.554-0.131-0.867-0.522-1.035-1.048c-0.117-0.367-0.056-0.737,0.012-1.094c0.341-1.797,1.366-3.006,3.125-3.555 c0.357-0.112,0.731-0.166,1.105-0.166c0.94,0.001,1.881,0.001,2.821-0.001c0.128,0,0.257-0.012,0.385-0.021 c0.702-0.051,1.166-0.511,1.22-1.352c0.004-0.064,0-0.129,0.001-0.193c0.011-0.788,0.605-1.396,1.393-1.425 c0.787-0.029,1.438,0.527,1.493,1.318c0.076,1.083-0.265,2.046-0.913,2.907C16.903,11.751,16.819,11.816,16.792,11.923z M8.249,10.436c-0.258-0.008-0.571,0.018-0.882-0.035c-0.536-0.09-0.876-0.39-1.02-0.916C6.19,8.912,6.25,8.388,6.698,7.96 C7.154,7.526,7.694,7.4,8.285,7.645c0.52,0.216,0.859,0.731,0.89,1.293C9.2,9.382,9.178,9.828,9.182,10.272 c0.001,0.116-0.043,0.167-0.161,0.165C8.781,10.434,8.542,10.436,8.249,10.436z M21.682,0H2.318C1.102,0,0.116,0.986,0.116,2.202 v19.317c0,1.37,1.111,2.481,2.481,2.481h18.807c1.37,0,2.481-1.111,2.481-2.481V2.202C23.884,0.986,22.898,0,21.682,0z M20.125,12.473c0.519,0.804,0.733,1.69,0.677,2.657c-0.108,1.886-1.413,3.474-3.25,3.916c-2.585,0.623-4.566-0.923-5.233-2.794 c-0.109-0.304-0.16-0.622-0.224-0.985c-0.068,0.414-0.115,0.789-0.264,1.134c-0.697,1.617-1.884,2.603-3.665,2.799 c-2.104,0.232-4.048-1.067-4.632-3.084c-0.25-0.863-0.175-1.747-0.068-2.625c0.08-0.653,0.321-1.268,0.632-1.848 c0.057-0.106,0.057-0.184-0.01-0.285c-0.561-0.845-0.779-1.777-0.7-2.784C3.43,8.035,3.56,7.52,3.805,7.038 C4.52,5.626,6.09,4.427,8.193,4.626c1.849,0.175,3.562,1.77,3.83,3.564c0.013,0.09,0.039,0.178,0.068,0.311 c0.044-0.241,0.076-0.439,0.118-0.636c0.344-1.63,1.94-3.335,4.201-3.357c2.292-0.021,3.99,1.776,4.31,3.446 c0.17,0.888,0.089,1.776-0.103,2.663c-0.112,0.517-0.31,1.008-0.524,1.492C20.034,12.245,20.043,12.345,20.125,12.473z"/></svg>
                            </span>
                            <h4 class="skill-card-name">XAMPP</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 128 128"><path fill="#F34F29" d="M124.737 58.378L69.621 3.264c-3.172-3.174-8.32-3.174-11.497 0L46.68 14.71l14.518 14.518c3.375-1.139 7.243-.375 9.932 2.314 2.703 2.706 3.461 6.607 2.294 9.993l13.992 13.993c3.385-1.167 7.292-.413 9.994 2.295 3.78 3.777 3.78 9.9 0 13.679a9.673 9.673 0 01-13.683 0 9.677 9.677 0 01-2.105-10.521L68.574 47.933l-.002 34.341a9.708 9.708 0 012.559 1.828c3.778 3.777 3.778 9.898 0 13.683-3.779 3.777-9.904 3.777-13.679 0-3.778-3.784-3.778-9.905 0-13.683a9.65 9.65 0 013.167-2.11V47.333a9.581 9.581 0 01-3.167-2.111c-2.862-2.86-3.551-7.06-2.083-10.576L41.056 20.333 3.264 58.123a8.133 8.133 0 000 11.5l55.117 55.114c3.174 3.174 8.32 3.174 11.499 0l54.858-54.858a8.135 8.135 0 00-.001-11.501z"/></svg>
                            </span>
                            <h4 class="skill-card-name">Git</h4>
                        </div>
                        <div class="skill-card">
                            <span class="skill-icon">
                                <svg viewBox="0 0 128 128" fill="#FFFFFF"><path fill-rule="evenodd" clip-rule="evenodd" d="M64 5.103c-33.347 0-60.388 27.035-60.388 60.388 0 26.682 17.303 49.317 41.297 57.303 3.017.56 4.125-1.31 4.125-2.905 0-1.44-.056-6.197-.082-11.243-16.8 3.653-20.345-7.125-20.345-7.125-2.747-6.98-6.705-8.836-6.705-8.836-5.48-3.748.413-3.67.413-3.67 6.063.425 9.257 6.223 9.257 6.223 5.386 9.23 14.127 6.562 17.573 5.02.542-3.903 2.107-6.568 3.834-8.076-13.413-1.525-27.514-6.704-27.514-29.843 0-6.593 2.36-11.98 6.223-16.21-.628-1.52-2.695-7.662.584-15.98 0 0 5.07-1.623 16.61 6.19C53.7 35 58.867 34.327 64 34.304c5.13.023 10.3.694 15.127 2.033 11.526-7.813 16.59-6.19 16.59-6.19 3.287 8.317 1.22 14.46.593 15.98 3.872 4.23 6.215 9.617 6.215 16.21 0 23.194-14.127 28.3-27.574 29.796 2.167 1.874 4.097 5.55 4.097 11.183 0 8.08-.07 14.583-.07 16.572 0 1.607 1.088 3.49 4.148 2.897 23.98-7.994 41.263-30.622 41.263-57.294C124.388 32.14 97.35 5.104 64 5.104z"/><path d="M26.484 91.806c-.133.3-.605.39-1.035.185-.44-.196-.685-.605-.543-.906.13-.31.603-.395 1.04-.188.44.197.69.61.537.91zm2.446 2.729c-.287.267-.85.143-1.232-.28-.396-.42-.47-.983-.177-1.254.298-.266.844-.14 1.24.28.394.426.472.984.17 1.255zM31.312 98.012c-.37.258-.976.017-1.35-.52-.37-.538-.37-1.183.01-1.44.373-.258.97-.025 1.35.507.368.545.368 1.19-.01 1.452zm3.261 3.361c-.33.365-1.036.267-1.552-.23-.527-.487-.674-1.18-.343-1.544.336-.366 1.045-.264 1.564.23.527.486.686 1.18.333 1.543zm4.5 1.951c-.147.473-.825.688-1.51.486-.683-.207-1.13-.76-.99-1.238.14-.477.823-.7 1.512-.485.683.206 1.13.756.988 1.237zm4.943.361c.017.498-.563.91-1.28.92-.723.017-1.308-.387-1.315-.877 0-.503.568-.91 1.29-.924.717-.013 1.306.387 1.306.88zm4.598-.782c.086.485-.413.984-1.126 1.117-.7.13-1.35-.172-1.44-.653-.086-.498.422-.997 1.122-1.126.714-.123 1.354.17 1.444.663zm0 0"/></svg>
                            </span>
                            <h4 class="skill-card-name">GitHub</h4>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- =================== PROJECTS SECTION =================== -->
    <section id="projects" class="section projects-section">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">Portfolio</span>
                <h2 class="section-title">My Projects</h2>
                <p class="section-subtitle">A selection of work I've built</p>
            </div>
            <?php if (empty($projects)): ?>
            <div class="no-projects reveal">
                <div class="no-projects-icon">&#128193;</div>
                <p>No projects yet. Check back soon!</p>
            </div>
            <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $i => $project):
                    $imgSrc = get_project_image_src($project['image'] ?? $project['image_url'] ?? '');
                ?>
                <div class="project-card reveal" style="--stagger: <?= $i * 0.1 ?>s">
                    <div class="project-image-wrapper">
                        <img src="<?= htmlspecialchars($imgSrc) ?>"
                             alt="<?= htmlspecialchars($project['title']) ?>"
                             class="project-image" loading="lazy"
                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80';">
                        <div class="project-overlay">
                            <div class="project-overlay-content">
                                <?php if (!empty($project['github_url'])): ?>
                                    <a href="<?= htmlspecialchars($project['github_url']) ?>" target="_blank" rel="noopener noreferrer" class="overlay-btn">&#128279; GitHub</a>
                                <?php endif; ?>
                                <?php if (!empty($project['live_url'])): ?>
                                    <a href="<?= htmlspecialchars($project['live_url']) ?>" target="_blank" rel="noopener noreferrer" class="overlay-btn">&#127760; Live Demo</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="project-body">
                        <h3 class="project-title"><?= htmlspecialchars($project['title']) ?></h3>
                        <p class="project-desc"><?= htmlspecialchars($project['description']) ?></p>
                        <div class="project-tech">
                            <?php
                            $techs = array_map('trim', explode(',', $project['technologies']));
                            foreach (array_slice($techs, 0, 5) as $tech): ?>
                                <span class="tech-tag"><?= htmlspecialchars($tech) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="project-links">
                            <?php if (!empty($project['github_url'])): ?>
                                <a href="<?= htmlspecialchars($project['github_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-github">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0 1 12 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.929.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                                    GitHub
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($project['live_url'])): ?>
                                <a href="<?= htmlspecialchars($project['live_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-demo">
                                    &#127760; Live Demo
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- =================== CERTIFICATES SECTION =================== -->
    <section id="certificates" class="section certificates-section">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">Certifications</span>
                <h2 class="section-title">Certificates &amp; Achievements</h2>
                <p class="section-subtitle">Verified training credentials, workshop completions, and professional accomplishments</p>
            </div>

            <?php if (empty($certificates)): ?>
            <div class="no-certificates reveal">
                <div class="no-certificates-icon">&#127941;</div>
                <h3 class="no-certificates-title">No Certificates Published Yet</h3>
                <p class="no-certificates-desc">Verified certificates and training credentials will be showcased here once updated. Please check back soon or browse my projects above.</p>
            </div>
            <?php else: ?>
            <div class="certificates-grid">
                <?php foreach ($certificates as $i => $cert):
                    $fileSrc = get_certificate_file_src($cert['file_path'] ?? '');
                    $fileType = $cert['file_type'] ?? 'image';
                    $hasFile = !empty($cert['file_path']);
                    $dateFormatted = !empty($cert['issue_date']) ? date('F Y', strtotime($cert['issue_date'])) : '';
                ?>
                <div class="certificate-card reveal" style="--stagger: <?= $i * 0.1 ?>s">
                    <div class="certificate-preview-wrapper">
                        <?php if ($hasFile && $fileType === 'image'): ?>
                            <img src="<?= htmlspecialchars($fileSrc) ?>"
                                 alt="<?= htmlspecialchars($cert['title']) ?> Certificate"
                                 class="certificate-image" loading="lazy"
                                 onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'certificate-placeholder-icon\'>&#127941;</div>';">
                            <div class="certificate-overlay">
                                <a href="<?= htmlspecialchars($fileSrc) ?>" target="_blank" rel="noopener noreferrer" class="overlay-btn">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <span>View Full Certificate</span>
                                </a>
                            </div>
                        <?php elseif ($hasFile && $fileType === 'pdf'): ?>
                            <div class="certificate-pdf-badge-wrapper">
                                <div class="certificate-pdf-icon">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                </div>
                                <span class="certificate-pdf-label">PDF Certificate Document</span>
                            </div>
                            <div class="certificate-overlay">
                                <a href="<?= htmlspecialchars($fileSrc) ?>" target="_blank" rel="noopener noreferrer" class="overlay-btn">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    <span>Open PDF Document</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="certificate-badge-placeholder">
                                <div class="certificate-placeholder-icon">
                                    <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="6"></circle><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path></svg>
                                </div>
                                <span class="certificate-badge-label">Verified Credential</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="certificate-body">
                        <div class="certificate-meta">
                            <span class="certificate-org"><?= htmlspecialchars($cert['organization']) ?></span>
                            <?php if (!empty($dateFormatted)): ?>
                                <span class="certificate-date"><?= htmlspecialchars($dateFormatted) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="certificate-title"><?= htmlspecialchars($cert['title']) ?></h3>
                        <?php if (!empty($cert['description'])): ?>
                            <p class="certificate-desc"><?= htmlspecialchars($cert['description']) ?></p>
                        <?php endif; ?>

                        <div class="certificate-actions">
                            <?php if ($hasFile): ?>
                                <a href="<?= htmlspecialchars($fileSrc) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-certificate">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    <span><?= $fileType === 'pdf' ? 'View PDF' : 'View Certificate' ?></span>
                                </a>
                            <?php else: ?>
                                <span class="certificate-verified-tag">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Verified Credential</span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- =================== EXPERIENCE SECTION =================== -->
    <section id="experience" class="section experience-section">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">Experience</span>
                <h2 class="section-title">Education &amp; Experience</h2>
            </div>
            <div class="timeline">
                <!-- Education -->
                <div class="timeline-item reveal">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-period">2022 &ndash; 2026</div>
                        <h3 class="timeline-title">Bachelor of Science in Information Technology</h3>
                        <div class="timeline-place">STI College Lucena &bull; Lucena City, Philippines</div>
                        <p class="timeline-desc">Completed a four-year Bachelor of Science in Information Technology program, building a practical foundation in computer systems, software configuration, networking basics, database management, and web application structure.</p>
                        <div class="timeline-tags">
                            <span>IT Fundamentals</span><span>Computer Systems</span><span>Basic Networking</span><span>Database Management</span><span>Web Technologies</span>
                        </div>
                    </div>
                </div>

                <!-- Internship / OJT -->
                <div class="timeline-item reveal">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-period">March 2026 &ndash; June 2026</div>
                        <h3 class="timeline-title">IT Intern / On-the-Job Trainee</h3>
                        <div class="timeline-place">Commission on Elections (COMELEC) &bull; Lucena City</div>
                        <p class="timeline-desc">Served as an IT support intern providing hands-on technical assistance, hardware preparation, software installation, and connectivity troubleshooting for office personnel and voter registration operations.</p>
                        <ul class="experience-resp-list">
                            <li>Assisted with computer setup and preparation for voter registration activities at the main office and satellite registration sites.</li>
                            <li>Installed and configured Microsoft Office and other required software.</li>
                            <li>Assisted with basic computer troubleshooting and Wi-Fi connectivity issues, including Wi-Fi username and password resets.</li>
                            <li>Helped configure computers and display settings for seminars and office activities.</li>
                            <li>Assisted with wired network connectivity so registration computers could access a shared voter database.</li>
                            <li>Supported voter registration and certification activities, including operating equipment used to capture photographs, fingerprints, and signatures.</li>
                            <li>Provided general technical assistance to staff during office operations.</li>
                        </ul>
                        <div class="timeline-tags">
                            <span>IT Support</span><span>Hardware &amp; PC Setup</span><span>Software Installation</span><span>Network Connectivity</span><span>Technical Assistance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =================== CONTACT SECTION =================== -->
    <section id="contact" class="section contact-section">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag">Contact</span>
                <h2 class="section-title">Let's Connect</h2>
                <p class="section-subtitle">I'm open to opportunities, collaborations, and conversations</p>
            </div>
            <div class="contact-grid">
                <div class="contact-info reveal">
                    <h3 class="contact-heading">Get In Touch</h3>
                    <p class="contact-text">Feel free to reach out whether you have a project in mind, a job opportunity, or just want to say hello. I'm always happy to connect!</p>
                    <div class="contact-items">
                        <div class="contact-item">
                            <div class="contact-icon-wrapper">&#128222;</div>
                            <div>
                                <div class="contact-label">Phone</div>
                                <a href="tel:09762022723" class="contact-value">09762022723</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon-wrapper">&#128231;</div>
                            <div>
                                <div class="contact-label">Email</div>
                                <a href="mailto:kianaguinaldo7@gmail.com" class="contact-value">kianaguinaldo7@gmail.com</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon-wrapper">&#127758;</div>
                            <div>
                                <div class="contact-label">Location</div>
                                <span class="contact-value">Philippines</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contact-form-wrapper reveal">
                    <form class="contact-form" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contactName">Your Name</label>
                                <input type="text" id="contactName" name="name" placeholder="John Doe" required>
                            </div>
                            <div class="form-group">
                                <label for="contactEmail">Your Email</label>
                                <input type="email" id="contactEmail" name="email" placeholder="john@example.com" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="contactSubject">Subject</label>
                            <input type="text" id="contactSubject" name="subject" placeholder="How can I help?" required>
                        </div>
                        <div class="form-group">
                            <label for="contactMessage">Message</label>
                            <textarea id="contactMessage" name="message" rows="5" placeholder="Your message..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">Send Message &#9992;</button>
                        <div id="formMessage" class="form-feedback" style="display:none"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <span class="logo-text">Kian</span><span class="logo-dot">.</span>
                    <p>Kian Lee Raian M. Aguinaldo<br>BS Information Technology Graduate</p>
                </div>
                <div class="footer-links">
                    <a href="#home">Home</a>
                    <a href="#about">About</a>
                    <a href="#projects">Projects</a>
                    <a href="#contact">Contact</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Kian Lee Raian M. Aguinaldo. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/particles.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

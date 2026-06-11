<?php
require_once 'config.php';

// Safe fetch query helper
function fetch_all_safe($pdo, $sql, $params = []) {
    if (!$pdo) return [];
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Fetch dynamic elements
$gallery_items = fetch_all_safe($pdo, "SELECT * FROM gallery ORDER BY upload_date DESC");

$advisors = fetch_all_safe($pdo, "SELECT * FROM members WHERE position = 'Advisor' AND status = 'approved' ORDER BY student_id ASC");
$president = fetch_all_safe($pdo, "SELECT * FROM members WHERE position = 'President' AND status = 'approved' ORDER BY student_id ASC");
$vps = fetch_all_safe($pdo, "SELECT * FROM members WHERE position = 'Vice President' AND status = 'approved' ORDER BY student_id ASC");
$gs = fetch_all_safe($pdo, "SELECT * FROM members WHERE position = 'General Secretary' AND status = 'approved' ORDER BY student_id ASC");
$presidium = fetch_all_safe($pdo, "SELECT * FROM members WHERE position = 'Presidium Member' AND status = 'approved' ORDER BY student_id ASC");
$other_leaders = fetch_all_safe($pdo, "SELECT * FROM members WHERE position NOT IN ('Advisor', 'President', 'Vice President', 'General Secretary', 'Presidium Member', 'Member') AND status = 'approved' ORDER BY student_id ASC");
$members = fetch_all_safe($pdo, "SELECT * FROM members WHERE position = 'Member' AND status = 'approved' ORDER BY student_id ASC");

$notices = fetch_all_safe($pdo, "SELECT * FROM notices ORDER BY date DESC, created_at DESC LIMIT 5");
$campaigns = fetch_all_safe($pdo, "SELECT * FROM funding_campaigns ORDER BY deadline ASC, created_at DESC");
$jobs = fetch_all_safe($pdo, "SELECT * FROM job_offers WHERE status = 'active' ORDER BY created_at DESC");
$emergencies = fetch_all_safe($pdo, "SELECT * FROM emergency_cases WHERE status = 'active' ORDER BY created_at DESC");

// Fetch latest notifications (notices, funding campaigns, emergencies, and jobs) for the notification panel
$notif_notices = fetch_all_safe($pdo, "SELECT id, title, description, created_at, 'notice' AS type FROM notices ORDER BY created_at DESC LIMIT 3");
$notif_campaigns = fetch_all_safe($pdo, "SELECT id, title, description, created_at, 'funding' AS type FROM funding_campaigns ORDER BY created_at DESC LIMIT 3");
$notif_emergencies = fetch_all_safe($pdo, "SELECT id, person_name AS title, situation AS description, created_at, 'emergency' AS type FROM emergency_cases WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");
$notif_jobs = fetch_all_safe($pdo, "SELECT id, title, description, created_at, 'job' AS type FROM job_offers WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");

$all_notifications = array_merge($notif_notices, $notif_campaigns, $notif_emergencies, $notif_jobs);
// Sort notifications by created_at descending
usort($all_notifications, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$latest_notifications = array_slice($all_notifications, 0, 5);

// Fetch active contacts
$president_contact = fetch_all_safe($pdo, "SELECT * FROM contact_info WHERE role = 'President' LIMIT 1");
$gs_contact = fetch_all_safe($pdo, "SELECT * FROM contact_info WHERE role = 'General Secretary' LIMIT 1");

$pres_name = !empty($president_contact) ? $president_contact[0]['name'] : 'Chitrodip Sen';
$pres_phone = !empty($president_contact) ? $president_contact[0]['phone'] : '+880 1644 139432';
$pres_email = !empty($president_contact) ? $president_contact[0]['email'] : 'chitrodip@kuet.ac.bd';

$gs_name = !empty($gs_contact) ? $gs_contact[0]['name'] : 'Preetom Roy Shaibal';
$gs_phone = !empty($gs_contact) ? $gs_contact[0]['phone'] : '+880 1619 500255';
$gs_email = !empty($gs_contact) ? $gs_contact[0]['email'] : 'preetom@kuet.ac.bd';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sylhet Association of KUET</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime('css/style.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    
    <!-- Notification Toast System -->
    <?php if (isset($_SESSION['msg_status'])): ?>
        <?php 
        $status = $_SESSION['msg_status']; 
        $text = $_SESSION['msg_text'];
        unset($_SESSION['msg_status']);
        unset($_SESSION['msg_text']);
        ?>
        <div class="notification-toast" id="notif-toast" style="position: fixed; top: 90px; right: 20px; z-index: 2000; padding: 15px 25px; border-radius: 10px; font-weight: bold; box-shadow: 0 10px 30px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 15px; color: <?php echo $status === 'success' ? '#155724' : '#721c24'; ?>; background-color: <?php echo $status === 'success' ? '#d4edda' : '#f8d7da'; ?>; border: 1px solid <?php echo $status === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <span><?php echo htmlspecialchars($text); ?></span>
            <button onclick="document.getElementById('notif-toast').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: inherit; font-weight: bold; line-height: 1;">&times;</button>
        </div>
        <script>
            setTimeout(function() {
                var toast = document.getElementById('notif-toast');
                if (toast) {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.5s ease';
                    setTimeout(function() { toast.remove(); }, 500);
                }
            }, 6000);
        </script>
    <?php endif; ?>

    <header>
        <div>
            <img src="img/logo.png" alt="Sylhet Association of KUET Logo" width="100" height="100" onerror="this.src='img/logo.png'">
        </div>
        <button id="nav-toggle" type="button" class="nav-toggle" aria-label="Toggle navigation" aria-controls="site-nav" aria-expanded="false">☰</button>
        <nav id="site-nav">
            <ul>
                <li><a href="#Home">Home</a></li>
                <li><a href="#About">About</a></li>
                <li><a href="#Events">Events</a></li>
                <li><a href="#Gallery">Gallery</a></li>
                <li><a href="#Members">Members</a></li>
                <li><a href="#Notices">Notices</a></li>
                <li><a href="#Funding">Funding</a></li>
                <li><a href="#Jobs">Jobs</a></li>
                <li><a href="#Emergency">Emergency</a></li>
                <li><a href="#Contact">Contact</a></li>
            </ul>
        </nav>
        <div class="header-actions">
            <div class="notif-bell-container" id="notif-bell-trigger">
                <button class="notif-bell-btn" aria-label="Notifications" onclick="toggleNotifPanel(event)">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <span class="notif-badge" id="notif-badge" style="display: none;">0</span>
                </button>
            </div>
            <div class="menu-btn">
                <button onclick="document.getElementById('joinnow-modal').style.display='flex'" class="signup">Join Now</button>
            </div>
        </div>
    </header>
    <main>
        <section class="home" id="Home">
            <div class="container">
                <div class="head">
                    <h1>Welcome to <br><span class="hero-title-accent">Sylhet Association of KUET!</span></h1>
                    <p>A community of Sylheti students united by culture, heritage, and brotherhood — building bonds beyond the classroom.</p>
                    <p>We grow together through friendship, support, and meaningful experiences beyond academics.</p>
                    <p>A close-knit community preserving Sylheti traditions while encouraging innovation, leadership, and friendship.</p>
                    <p>আমরা সিলটি ফুয়া-ফুয়ানি, সংস্কৃতি আর ঐতিহ্য নিয়া একলগে থাকি।</p>
                    <p>বন্ধুত্ব, সহায়তা আর ভালোবাসায় আমরা একলগে আগাইতাছি।</p>
                    <p>আমরা শুধু ইঞ্জিনিয়ার না, আমরা সিলটির গর্ব — কাজ আর দক্ষতায় প্রমাণ দেই।</p>
                    <p>এখানে প্রতিটা সম্পর্ক হয় জীবনের জন্য, আর প্রতিটা মুহূর্ত হয় স্মরণীয়।</p>
                    <p>এখানে শুধু সংগঠন না, এইটা হইলো সিলটি হৃদয়ের মিলনস্থল।</p>
                </div>
                <div class="herobtn">
                    <div><a href="javascript:void(0)" class="becomeamember" onclick="document.getElementById('joinnow-modal').style.display='flex'">Become a Member</a></div>
                    <div><a href="#Events" class="viewevents">View Events</a></div>
                </div>
                <hr>
                <div class="box">
                    <div class="card">
                        <h3 style="color: rgb(255, 217, 0);">50+</h3>
                        <p>Active Members</p>
                    </div>
                    <div class="card">
                        <h3 style="color: rgb(255, 217, 0);">30+</h3>
                        <p>Events Hosted</p>
                    </div>
                    <div class="card">
                        <h3 style="color: rgb(255, 217, 0);">10+</h3>
                        <p>Years of Legacy</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="about" id="About">
            <div class="abouttext">
                <h2>About Us</h2>
                <p>The Sylhet Association of KUET is a vibrant community of Sylheti students at Khulna University of Engineering & Technology (KUET). We are dedicated to fostering a sense of belonging, cultural pride, and camaraderie among our members. Our association serves as a platform for students to connect, share their heritage, and create lasting memories together.</p>
                <p>Our mission is to promote Sylheti culture, support our members in their academic and personal growth, and organize events that celebrate our unique identity. We believe in the power of unity and friendship, and we strive to create an inclusive environment where everyone feels welcome.</p>
                <p>Whether you're a new student looking to make friends or a senior member wanting to give back to the community, the Sylhet Association of KUET is here for you. Join us in celebrating our culture, building lifelong friendships, and making the most of your time at KUET!</p>
            </div>
            <div class="aboutimg">
                <img src="img/about.png" alt="About Us Image" width="400" height="300">
            </div>
        </section>
        <section class="events" id="Events">
            <div class="events-container">
                <div class="section-title">
                    <h2>Events</h2>
                    <p>Moments that bring our community together through celebration, care, and shared memories.</p>
                </div>
                <div class="events-grid">
                    <article class="event-card">
                        <img src="img/admission.png" alt="Admission test help">
                        <h3>Admission Test</h3>
                        <p>Every year, KUET organizes its admission test, where many admission candidates come from Sylhet. During this time, we help the candidates by providing proper instructions, such as locating their admission seats, supplying drinking water, arranging accommodation for overnight stays, and guiding them about the travel route from Sylhet to Khulna.</p>
                    </article>
                    <article class="event-card">
                        <img src="img/bbq.png" alt="BBQ night gathering">
                        <h3>BBQ Night</h3>
                        <p>The Sylhet Association of KUET organizes a BBQ Night every year to create enjoyable moments among the students from Sylhet. It is a wonderful gathering filled with delicious food, fun, and friendship, where juniors and seniors bond together like a family.</p>
                    </article>
                    <article class="event-card">
                        <img src="img/campfire.png" alt="Campfire program">
                        <h3>Campfire</h3>
                        <p>The Sylhet Association of KUET organizes a Campfire program every year where we warmly welcome the freshers and give farewell to our respected seniors. It is a memorable event filled with fun, cultural activities, and heartfelt moments that strengthen the bond among all members of the association.</p>
                    </article>
                    <article class="event-card">
                        <img src="img/help.jpg" alt="Helping hands program">
                        <h3>Helping Hands</h3>
                        <p>Through the "Helping Hand" program, we try to support poor and helpless people during Ramadan by distributing gifts and essential items. We also stand beside flood-affected people by providing relief and necessary support during difficult times. This program reflects the spirit of humanity, kindness, and social responsibility.</p>
                    </article>
                    <article class="event-card">
                        <img src="img/iftar.jpg" alt="Iftar assembly">
                        <h3>Iftar Gathering</h3>
                        <p>The Sylhet Association of KUET also arranges beautiful Iftar gatherings during Ramadan, where seniors and juniors come together in Sylhet. It is a special moment to share food, strengthen our bond, and enjoy the blessings of Ramadan as one family.</p>
                    </article>
                    <article class="event-card">
                        <img src="img/playing.jpg" alt="Sports matches">
                        <h3>Playing Time</h3>
                        <p>The Sylhet Association of KUET also organizes friendly cricket and football matches among its members. These sports events create a lively and competitive atmosphere where seniors and juniors play together, enjoy teamwork, and strengthen their bonding beyond academic life.</p>
                    </article>
                </div>
            </div>
        </section>
        
        <section class="gallery" id="Gallery">
            <div class="gallery-container">
                <div class="section-title">
                    <h2>Gallery</h2>
                    <p>A visual collection of our moments, memories, and celebrations together.</p>
                </div>
                <div class="gallery-grid">
                    <?php if (empty($gallery_items)): ?>
                        <!-- Fallback static figures -->
                        <figure class="gallery-item gallery-item--featured">
                            <img src="img/gallery/476246187_1172976380844428_1202847445971021648_n.jpg" alt="Sylhet Association photo 1">
                        </figure>
                        <figure class="gallery-item gallery-item--wide">
                            <img src="img/gallery/480958285_3617746228523215_1006179549148778965_n.jpg" alt="Sylhet Association photo 2">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/481014876_4094615337462841_9190408291765314636_n.jpg" alt="Sylhet Association photo 3">
                        </figure>
                        <figure class="gallery-item gallery-item--tall">
                            <img src="img/gallery/485909190_3643282762636228_2968566113269985611_n.jpg" alt="Sylhet Association photo 4">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/486408712_3642953786002459_5175100300069450156_n.jpg" alt="Sylhet Association photo 5">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/486441221_3642954189335752_4906628804405918806_n.jpg" alt="Sylhet Association photo 6">
                        </figure>
                        <figure class="gallery-item gallery-item--wide">
                            <img src="img/gallery/486546752_3643282759302895_8601998444467959151_n.jpg" alt="Sylhet Association photo 7">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/489533973_2543308612682652_7032182446963710717_n.jpg" alt="Sylhet Association photo 8">
                        </figure>
                        <figure class="gallery-item gallery-item--tall">
                            <img src="img/gallery/489624521_3658402837790887_6822467484806580957_n.jpg" alt="Sylhet Association photo 9">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/489638478_3658403111124193_6607232725494849059_n.jpg" alt="Sylhet Association photo 10">
                        </figure>
                        <figure class="gallery-item gallery-item--featured">
                            <img src="img/gallery/489965583_3658402831124221_6182614192672569997_n.jpg" alt="Sylhet Association photo 11">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/490313268_3658402737790897_8647833558560052012_n.jpg" alt="Sylhet Association photo 12">
                        </figure>
                        <figure class="gallery-item gallery-item--wide">
                            <img src="img/gallery/490730338_2546234992390014_1086272061069171795_n.jpg" alt="Sylhet Association photo 13">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/494427771_3681489198815584_765480824747456412_n.jpg" alt="Sylhet Association photo 14">
                        </figure>
                        <figure class="gallery-item gallery-item--tall">
                            <img src="img/gallery/496943296_3878735995722847_4227356908522136736_n.jpg" alt="Sylhet Association photo 15">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/52918138_264095187825336_2324994362875838464_n.jpg" alt="Sylhet Association photo 16">
                        </figure>
                        <figure class="gallery-item gallery-item--wide">
                            <img src="img/gallery/549163110_3991900147739764_115848000983495266_n.jpg" alt="Sylhet Association photo 17">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/549194770_3991900164406429_3784078091420324174_n (1).jpg" alt="Sylhet Association photo 18">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/549194770_3991900164406429_3784078091420324174_n.jpg" alt="Sylhet Association photo 19">
                        </figure>
                        <figure class="gallery-item gallery-item--featured">
                            <img src="img/gallery/549790039_3991900131073099_8416961167657777819_n (1).jpg" alt="Sylhet Association photo 20">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/549790039_3991900131073099_8416961167657777819_n.jpg" alt="Sylhet Association photo 21">
                        </figure>
                        <figure class="gallery-item gallery-item--wide">
                            <img src="img/gallery/61215277_2554099127934460_4286308066405646336_n.jpg" alt="Sylhet Association photo 22">
                        </figure>
                        <figure class="gallery-item">
                            <img src="img/gallery/653717783_4157151651214612_8062812811457542165_n.jpg" alt="Sylhet Association photo 23">
                        </figure>
                    <?php else: ?>
                        <!-- Dynamic masonry image loads -->
                        <?php foreach ($gallery_items as $photo): ?>
                            <?php 
                            $class = "gallery-item";
                            if ($photo['category'] === 'featured') $class .= " gallery-item--featured";
                            elseif ($photo['category'] === 'wide') $class .= " gallery-item--wide";
                            elseif ($photo['category'] === 'tall') $class .= " gallery-item--tall";
                            ?>
                            <figure class="<?php echo $class; ?>">
                                <img src="<?php echo htmlspecialchars($photo['filename']); ?>" alt="<?php echo htmlspecialchars($photo['title'] ?? ''); ?>">
                            </figure>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <section class="people" id="Members">
            <div class="people-container">
                <div class="section-title">
                    <h2>Honorable Members</h2>
                    <p>Our respectable leadership, advisors, and members.</p>
                </div>

                <!-- Advisors -->
                <div class="people-group">
                    <h3>Advisors</h3>
                    <div class="people-grid people-grid--advisors">
                        <?php if (empty($advisors)): ?>
                            <figure class="people-card people-card--tallimg">
                                <img src="img/People/Student_Advisor_1.jpg" alt="Student Advisor 1">
                                <div class="people-role">Advisor</div>
                                <div class="people-name">Engr. Sarkar Raj Kishore Bappa</div>
                                <div class="people-caption">BECM 2K14</div>
                            </figure>
                            <figure class="people-card people-card--tallimg">
                                <img src="img/People/Student_Advisor_2.jpg" alt="Student Advisor 2">
                                <div class="people-role">Advisor</div>
                                <div class="people-name">Engr. Sowmitro Bhattacharjee</div>
                                <div class="people-caption">ME 2K15</div>
                            </figure>
                            <figure class="people-card people-card--tallimg">
                                <img src="img/People/Student_Advisor_3.jpg" alt="Student Advisor 3">
                                <div class="people-role">Advisor</div>
                                <div class="people-name">Engr. Amit Deb Roy Anik</div>
                                <div class="people-caption">EEE 2K16</div>
                            </figure>
                            <figure class="people-card people-card--tallimg">
                                <img src="img/People/Student_Advisor_4.jpg" alt="Student Advisor 4">
                                <div class="people-role">Advisor</div>
                                <div class="people-name">Engr. Altaf Hussain Chowdhury</div>
                                <div class="people-caption">IEM 2K17</div>
                            </figure>
                            <figure class="people-card people-card--tallimg">
                                <img src="img/People/Student_Advisor_5.jpg" alt="Student Advisor 5">
                                <div class="people-role">Advisor</div>
                                <div class="people-name">Engr. Mohammad Mohiuddin</div>
                                <div class="people-caption">CE 2K18</div>
                            </figure>
                            <figure class="people-card people-card--tallimg">
                                <img src="img/People/Student_Advisor_6.jpg" alt="Student Advisor 6">
                                <div class="people-role">Advisor</div>
                                <div class="people-name">Engr. Bidyut Kumar Das</div>
                                <div class="people-caption">CSE 2K19</div>
                            </figure>
                        <?php else: ?>
                            <?php foreach ($advisors as $m): ?>
                                <figure class="people-card people-card--tallimg">
                                    <img src="<?php echo htmlspecialchars($m['photo'] ? $m['photo'] : 'img/People/default.png'); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" onerror="this.src='img/People/default.png'">
                                    <div class="people-role">Advisor</div>
                                    <div class="people-name"><?php echo htmlspecialchars($m['name']); ?></div>
                                    <div class="people-caption"><?php echo htmlspecialchars($m['department'] . ' ' . $m['batch']); ?></div>
                                </figure>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- President -->
                <div class="people-group">
                    <h3>President</h3>
                    <div class="people-grid people-grid--leaders">
                        <?php if (empty($president)): ?>
                            <figure class="people-card people-card--tallimg">
                                <img src="img/People/President.jpg" alt="President">
                                <div class="people-role">President</div>
                                <div class="people-name">Chitrodip Sen</div>
                                <div class="people-caption">ME 2K20</div>
                            </figure>
                        <?php else: ?>
                            <?php foreach ($president as $m): ?>
                                <figure class="people-card people-card--tallimg">
                                    <img src="<?php echo htmlspecialchars($m['photo'] ? $m['photo'] : 'img/People/default.png'); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" onerror="this.src='img/People/default.png'">
                                    <div class="people-role">President</div>
                                    <div class="people-name"><?php echo htmlspecialchars($m['name']); ?></div>
                                    <div class="people-caption"><?php echo htmlspecialchars($m['department'] . ' ' . $m['batch']); ?></div>
                                </figure>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Vice President -->
                <div class="people-group">
                    <h3>Vice President</h3>
                    <div class="people-grid people-grid--leaders">
                        <?php if (empty($vps)): ?>
                            <figure class="people-card">
                                <img src="img/People/Vice_president_1.jpg" alt="Vice President 1">
                                <div class="people-role">Vice President</div>
                                <div class="people-name">Amit Kairy</div>
                                <div class="people-caption">CSE 2K20</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Vice_president_2.jpg" alt="Vice President 2">
                                <div class="people-role">Vice President</div>
                                <div class="people-name">Abu Omayer</div>
                                <div class="people-caption">CSE 2K20</div>
                            </figure>
                        <?php else: ?>
                            <?php foreach ($vps as $m): ?>
                                <figure class="people-card">
                                    <img src="<?php echo htmlspecialchars($m['photo'] ? $m['photo'] : 'img/People/default.png'); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" onerror="this.src='img/People/default.png'">
                                    <div class="people-role">Vice President</div>
                                    <div class="people-name"><?php echo htmlspecialchars($m['name']); ?></div>
                                    <div class="people-caption"><?php echo htmlspecialchars($m['department'] . ' ' . $m['batch']); ?></div>
                                </figure>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- General Secretary -->
                <div class="people-group">
                    <h3>General Secretary</h3>
                    <div class="people-grid people-grid--leaders">
                        <?php if (empty($gs)): ?>
                            <figure class="people-card people-card--tallimg">
                                <img src="img/People/General_Secretary.jpg" alt="General Secretary">
                                <div class="people-role">General Secretary</div>
                                <div class="people-name">Preetom Roy Shaibal</div>
                                <div class="people-caption">CE 2K20</div>
                            </figure>
                        <?php else: ?>
                            <?php foreach ($gs as $m): ?>
                                <figure class="people-card people-card--tallimg">
                                    <img src="<?php echo htmlspecialchars($m['photo'] ? $m['photo'] : 'img/People/default.png'); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" onerror="this.src='img/People/default.png'">
                                    <div class="people-role">General Secretary</div>
                                    <div class="people-name"><?php echo htmlspecialchars($m['name']); ?></div>
                                    <div class="people-caption"><?php echo htmlspecialchars($m['department'] . ' ' . $m['batch']); ?></div>
                                </figure>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Presidium Members -->
                <div class="people-group">
                    <h3>Presidium Members</h3>
                    <div class="people-grid people-grid--leaders">
                        <?php if (empty($presidium)): ?>
                            <figure class="people-card">
                                <img src="img/People/Presidium_Member_1.jpg" alt="Presidium Member 1">
                                <div class="people-role">Presidium Member</div>
                                <div class="people-name">Niloy Das</div>
                                <div class="people-caption">CSE 2K20</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Presidium_Member_2.jpg" alt="Presidium Member 2">
                                <div class="people-role">Presidium Member</div>
                                <div class="people-name">Atikur Rahman Asib</div>
                                <div class="people-caption">CSE 2K20</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Presidium_Member_3.jpg" alt="Presidium Member 3">
                                <div class="people-role">Presidium Member</div>
                                <div class="people-name">Hemayetul Islam Jami</div>
                                <div class="people-caption">CSE 2K20</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Presidium_Member_4.jpg" alt="Presidium Member 4">
                                <div class="people-role">Presidium Member</div>
                                <div class="people-name">Syed Rukon</div>
                                <div class="people-caption">URP 2K20</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Presidium_Member_5.jpg" alt="Presidium Member 5">
                                <div class="people-role">Presidium Member</div>
                                <div class="people-name">Abdullah Al Shafi</div>
                                <div class="people-caption">CSE 2K20</div>
                            </figure>
                        <?php else: ?>
                            <?php foreach ($presidium as $m): ?>
                                <figure class="people-card">
                                    <img src="<?php echo htmlspecialchars($m['photo'] ? $m['photo'] : 'img/People/default.png'); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" onerror="this.src='img/People/default.png'">
                                    <div class="people-role">Presidium Member</div>
                                    <div class="people-name"><?php echo htmlspecialchars($m['name']); ?></div>
                                    <div class="people-caption"><?php echo htmlspecialchars($m['department'] . ' ' . $m['batch']); ?></div>
                                </figure>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Executive & Sub-Leaders -->
                <div class="people-group">
                    <h3>Others Posted Members</h3>
                    <div class="people-grid people-grid--memberss">
                        <?php if (empty($other_leaders)): ?>
                            <figure class="people-card">
                                <img src="img/People/Assistant_General_Secretary1.png" alt="Assistant General Secretary 1">
                                <div class="people-role">Assistant General Secretary</div>
                                <div class="people-name">Satyaki Shome</div>
                                <div class="people-caption">BECM 2K21</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Assistant_General_Secretary2.png" alt="Assistant General Secretary 2">
                                <div class="people-role">Assistant General Secretary</div>
                                <div class="people-name">Jakir Miah</div>
                                <div class="people-caption">ME 2K21</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Joint_Secratary.jpg" alt="Joint Secretary">
                                <div class="people-role">Joint Secretary</div>
                                <div class="people-name">Anmoy Datta</div>
                                <div class="people-caption">TE 2K21</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Organizing_Secretary_1.png" alt="Organizing Secretary 1">
                                <div class="people-role">Organizing Secretary</div>
                                <div class="people-name">Shuvro Das</div>
                                <div class="people-caption">LE 2K21</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Organizing_Secretary_2.png" alt="Organizing Secretary 2">
                                <div class="people-role">Organizing Secretary</div>
                                <div class="people-name">Rejaul Karim</div>
                                <div class="people-caption">ME 2K21</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Publicity_Secratary.jpg" alt="Publicity Secretary">
                                <div class="people-role">Publicity Secretary</div>
                                <div class="people-name">Sadia Mostafa</div>
                                <div class="people-caption">CSE 2K21</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Cultural_Secratary.jpg" alt="Cultural Secretary">
                                <div class="people-role">Cultural Secretary</div>
                                <div class="people-name">Dip Shekhar Datta</div>
                                <div class="people-caption">CSE 2K21</div>
                            </figure>
                            <figure class="people-card">
                                <img src="img/People/Treasour.png" alt="Treasurer">
                                <div class="people-role">Treasurer</div>
                                <div class="people-name">H.M. Azrof</div>
                                <div class="people-caption">CSE 2K21</div>
                            </figure>
                        <?php else: ?>
                            <?php foreach ($other_leaders as $m): ?>
                                <figure class="people-card">
                                    <img src="<?php echo htmlspecialchars($m['photo'] ? $m['photo'] : 'img/People/default.png'); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" onerror="this.src='img/People/default.png'">
                                    <div class="people-role"><?php echo htmlspecialchars($m['position']); ?></div>
                                    <div class="people-name"><?php echo htmlspecialchars($m['name']); ?></div>
                                    <div class="people-caption"><?php echo htmlspecialchars($m['department'] . ' ' . $m['batch']); ?></div>
                                </figure>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- General Members -->
                <div class="people-group">
                    <h3>Members</h3>
                    <div class="people-grid people-grid--members">
                        <?php if (empty($members)): ?>
                            <figure class="people-card"><img src="img/People/Member_1.jpg" alt="Member 1"><div class="people-role">Member</div><div class="people-name">Nurul Absar Shadik</div><div class="people-caption">CSE 2K22</div></figure>
                            <figure class="people-card"><img src="img/People/Member_2.jpg" alt="Member 2"><div class="people-role">Member</div><div class="people-name">Nurul Islam Rayhan</div><div class="people-caption">EEE 2K22</div></figure>
                            <figure class="people-card"><img src="img/People/Member_3.jpg" alt="Member 3"><div class="people-role">Member</div><div class="people-name">MasumUR Rahman Fokhrul </div><div class="people-caption">CSE 2K22</div></figure>
                            <figure class="people-card"><img src="img/People/Member_4.jpg" alt="Member 4"><div class="people-role">Member</div><div class="people-name">Golam Hossain Rabbi</div><div class="people-caption">MSE 2K22</div></figure>
                            <figure class="people-card"><img src="img/People/Member_5.jpg" alt="Member 5"><div class="people-role">Member</div><div class="people-name">Md. Bashir Ahmed</div><div class="people-caption">EEE 2K22</div></figure>
                            <figure class="people-card"><img src="img/People/Member_11.jpg" alt="Member 11"><div class="people-role">Member</div><div class="people-name">Sayanto Das</div><div class="people-caption">ECE 2K22</div></figure>
                            <figure class="people-card"><img src="img/People/Member_6.jpg" alt="Member 6"><div class="people-role">Member</div><div class="people-name">Jahed Ahmed</div><div class="people-caption">CSE 2K23</div></figure>
                            <figure class="people-card"><img src="img/People/Member_7.jpg" alt="Member 7"><div class="people-role">Member</div><div class="people-name">Shahriar Anan</div><div class="people-caption">ME 2K23</div></figure>
                            <figure class="people-card"><img src="img/People/Member_8.jpg" alt="Member 8"><div class="people-role">Member</div><div class="people-name">Tasnimul Hasan Rahat</div><div class="people-caption">EEE 2K23</div></figure>
                            <figure class="people-card"><img src="img/People/Member_9.jpg" alt="Member 9"><div class="people-role">Member</div><div class="people-name">Monojit Paul</div><div class="people-caption">CSE 2K23</div></figure>
                            <figure class="people-card"><img src="img/People/Member_10.jpg" alt="Member 10"><div class="people-role">Member</div><div class="people-name">Parthib Roy Dhrubo</div><div class="people-caption">EEE 2K23</div></figure>
                        <?php else: ?>
                            <?php foreach ($members as $m): ?>
                                <figure class="people-card">
                                    <img src="<?php echo htmlspecialchars($m['photo'] ? $m['photo'] : 'img/People/default.png'); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" onerror="this.src='img/People/default.png'">
                                    <div class="people-role">Member</div>
                                    <div class="people-name"><?php echo htmlspecialchars($m['name']); ?></div>
                                    <div class="people-caption"><?php echo htmlspecialchars($m['department'] . ' ' . $m['batch']); ?></div>
                                </figure>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="notices" id="Notices">
            <div class="section-title">
                <h2>Notices</h2>
                <p>Latest announcements and important updates for members.</p>
            </div>
            <div class="notice-container">
                <ul class="notice-list">
                    <?php if (empty($notices)): ?>
                        <!-- Fallback static notices -->
                        <li>
                            <strong>Admission Help (July 12, 09:00–17:00) —</strong> Volunteers needed to assist applicants with seating, accommodation and transport guidance. <a href="#Contact">Sign up</a> or contact the General Secretary.
                        </li>
                        <li>
                            <strong>General Meeting (June 20, 18:30) —</strong> Monthly member meeting at KUET cultural hall. Agenda: event planning, fund allocation, and membership drives.
                        </li>
                        <li>
                            <strong>BBQ Night & T‑shirt Sale (Aug 5) —</strong> Pre-order official association T‑shirts. Funds support event costs and community programs. See Funding section to donate or order.
                        </li>
                        <li>
                            <strong>Emergency Relief Drive —</strong> Ongoing collection for flood-affected families. Donations accepted (bank transfer / in-person / mobile). See Funding for details.
                        </li>
                    <?php else: ?>
                        <!-- Dynamic notices -->
                        <?php foreach ($notices as $n): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($n['title']); ?> (<?php echo date('M d, Y', strtotime($n['date'])); ?>) —</strong>
                                <?php echo nl2br(htmlspecialchars($n['description'])); ?>
                                <?php if ($n['attachment']): ?>
                                    <br><a href="<?php echo htmlspecialchars($n['attachment']); ?>" target="_blank" style="display: inline-block; margin-top: 8px; font-weight: bold; text-decoration: underline;">📄 View Attached File</a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </section>

        <section class="funding" id="Funding">
            <div class="section-title">
                <h2>Funding & Donations</h2>
                <p>Support our activities and relief programs — every contribution helps.</p>
            </div>
            <div class="funding-container" style="flex-wrap: wrap;">
                <?php if (empty($campaigns)): ?>
                    <!-- Fallback static funding cards -->
                    <div class="funding-card">
                        <h3>Flood Relief Campaign</h3>
                        <p>Target: ৳100,000 — purpose: immediate relief packages (food, medicine, shelter).</p>
                        <div class="funding-progress">
                            <div class="funding-progress__bar" style="width:42%"></div>
                        </div>
                        <p class="funding-meta">Raised: ৳42,000 — <a href="#Contact">Contact Treasurer to donate</a></p>
                    </div>
                    <div class="funding-card">
                        <h3>T‑shirt Fundraiser (Event Merch)</h3>
                        <p>Pre-orders help cover event costs and provide subsidized T‑shirts to members. Suggested donation: ৳300 per shirt.</p>
                        <ul>
                            <li>To order: message the Publicity Secretary or <a href="#Contact">use the contact form</a>.</li>
                            <li>Payment methods: bank transfer (ask Treasurer), mobile payment, or pay in person at KUET office.</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Dynamic campaign loads -->
                    <?php foreach ($campaigns as $camp): ?>
                        <?php 
                        $percent = $camp['goal_amount'] > 0 ? ($camp['raised_amount'] / $camp['goal_amount']) * 100 : 0;
                        if ($percent > 100) $percent = 100;
                        ?>
                        <div class="funding-card" style="flex: 1 1 45%; margin-bottom: 20px; box-sizing: border-box;">
                            <h3><?php echo htmlspecialchars($camp['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($camp['description'])); ?></p>
                            <p style="font-size: 13px; color: #425244; margin-top: 5px;"><strong>Purpose:</strong> <?php echo htmlspecialchars($camp['purpose']); ?></p>
                            <div class="funding-progress">
                                <div class="funding-progress__bar" style="width:<?php echo $percent; ?>%"></div>
                            </div>
                            <p class="funding-meta">
                                Raised: ৳<?php echo number_format($camp['raised_amount']); ?> of ৳<?php echo number_format($camp['goal_amount']); ?> (<?php echo round($percent); ?>%)
                            </p>
                            <button onclick="openDonationModal(<?php echo $camp['id']; ?>, '<?php echo htmlspecialchars(addslashes($camp['title'])); ?>')" class="emergency-btn" style="border: none; margin-top: 15px; font-weight: bold; font-family: inherit; cursor: pointer; background-color: #0c552b;">
                                Submit Donation Record
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="jobs" id="Jobs">
            <div class="section-title">
                <h2>Job Offers & Opportunities</h2>
                <p>Partnerships, internships and job postings for our members.</p>
            </div>
            <div class="jobs-grid">
                <?php if (empty($jobs)): ?>
                    <!-- Fallback static jobs -->
                    <article class="job-card">
                        <h3>Alumni: Web Developer (Intern/Junior)</h3>
                        <p>Alumni opportunity offering internship or junior role for final-year students. Stipend negotiable. Alumni employers: please <a href="#Contact">send job details</a>.</p>
                    </article>
                    <article class="job-card">
                        <h3>Part-time Tutor Positions</h3>
                        <p>Senior students can apply to tutor juniors (Math, Physics, Programming). Flexible schedule; paid by hour.</p>
                    </article>
                    <article class="job-card">
                        <h3>Event & Logistics Assistants</h3>
                        <p>Short-term paid roles for upcoming events — ideal for students seeking part-time work and experience.</p>
                    </article>
                <?php else: ?>
                    <!-- Dynamic jobs -->
                    <?php foreach ($jobs as $job): ?>
                        <article class="job-card">
                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                            <p style="font-size: 12px; margin-bottom: 8px; color: #666;">Company: <strong><?php echo htmlspecialchars($job['company']); ?></strong></p>
                            <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                            <?php if ($job['requirements']): ?>
                                <p style="font-size: 13px; color: #425244; margin-top: 8px;"><strong>Requirements:</strong> <?php echo htmlspecialchars($job['requirements']); ?></p>
                            <?php endif; ?>
                            <p style="font-size: 12px; font-weight: bold; color: #0c552b; margin-top: 10px;">
                                Contact: <code><?php echo htmlspecialchars($job['contact']); ?></code><br>
                                Deadline: <?php echo $job['deadline'] ? date('M d, Y', strtotime($job['deadline'])) : 'Open'; ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <p style="margin-top:12px;color:#425244">Alumni/employers: to post opportunities, email us or use the contact form so we can share with members.</p>
        </section>

        <section class="emergency" id="Emergency">
            <div class="section-title">
                <h2>Emergency & Support</h2>
                <p>Immediate contacts and quick-help resources for urgent situations.</p>
            </div>
            <div class="emergency-cards">
                <?php if (empty($emergencies)): ?>
                    <div class="emergency-card">
                        <h3>Blood & Medical Help</h3>
                        <p>If someone needs blood urgently, post the request in our Facebook group and contact the President/General Secretary for coordination. Local hospitals: Khulna Medical College Hospital, General Hospital (call ahead).</p>
                        <a class="emergency-btn" href="#Contact">Request Assistance</a>
                    </div>
                    <div class="emergency-card">
                        <h3>Quick Support & Info</h3>
                        <p>Lost phone, temporary accommodation, or minor transport help — message the help line or post in the group; volunteers often assist within an hour.</p>
                        <p>For non-life-threatening issues, use the contact form so we coordinate faster.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($emergencies as $em): ?>
                        <div class="emergency-card">
                            <h3><?php echo htmlspecialchars($em['person_name']); ?></h3>
                            <p><strong>Situation:</strong> <?php echo nl2br(htmlspecialchars($em['situation'])); ?></p>
                            <p style="margin-top: 8px;"><strong>Support Needed:</strong> <span style="font-weight: bold; color: #7a1b1b;"><?php echo nl2br(htmlspecialchars($em['support_needed'])); ?></span></p>
                            <p style="margin-top: 10px; font-weight: 600;">Contact: <?php echo htmlspecialchars($em['contact']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="contact" id="Contact">
            <div class="contact-container">
                <div class="section-title">
                    <h2>Contact Us</h2>
                    <p>Get in touch with the Sylhet Association of KUET. We'd love to hear from you!</p>
                </div>
                <div class="contact-content">
                    <div class="contact-info">
                        <div class="info-item">
                            <h3>Email</h3>
                            <p><a href="mailto:sylhetassoc.kuet@gmail.com">sylhetassoc.kuet@gmail.com</a></p>
                        </div>
                        <div class="info-item">
                            <h3>Location</h3>
                            <p>Khulna University of Engineering & Technology<br>Khulna-9203, Bangladesh</p>
                        </div>
                        <div class="info-item">
                            <h3>Social Media</h3>
                            <p><a href="https://facebook.com/groups/sylhetassociationkuet" target="_blank">Sylhet Association of KUET — Official Group</a></p>
                        </div>
                        <div class="info-item">
                            <h3>Phone Number</h3>
                            <p style="font-weight: 600;"><?php echo htmlspecialchars($pres_phone); ?> (President: <?php echo htmlspecialchars($pres_name); ?>)</p>
                            <p style="font-weight: 600;"><?php echo htmlspecialchars($gs_phone); ?> (General Secretary: <?php echo htmlspecialchars($gs_name); ?>)</p>
                        </div>
                    </div>
                    <form class="contact-form" action="process/submit_contact.php" method="POST">
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" placeholder="Your name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Your email" required>
                        </div>
                        <div class="form-group">
                            <label for="fullname-phone">Phone Number</label>
                            <input type="tel" id="fullname-phone" name="phone" placeholder="Your phone number">
                        </div>
                        <div class="form-group">
                            <label for="message_type">Inquiry Type</label>
                            <select id="message_type" name="message_type" required>
                                <option value="general">General Inquiry</option>
                                <option value="job offer">Job Opportunity Post</option>
                                <option value="emergency">Emergency / Medical Help</option>
                                <option value="funding request">Funding Request</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department">Department</label>
                            <select id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="CSE">Computer Science & Engineering (CSE)</option>
                                <option value="ME">Mechanical Engineering (ME)</option>
                                <option value="CE">Civil Engineering (CE)</option>
                                <option value="EEE">Electrical & Electronic Engineering (EEE)</option>
                                <option value="BECM">Bio-Medical Engineering (BECM)</option>
                                <option value="IPE">Industrial & Production Engineering (IPE)</option>
                                <option value="URP">Urban & Regional Planning (URP)</option>
                                <option value="TE">Textile Engineering (TE)</option>
                                <option value="LE">Leather Engineering (LE)</option>
                                <option value="MSE">Materials Science & Engineering (MSE)</option>
                                <option value="Arch">Architecture (Arch)</option>
                                <option value="BME">Biomedical Engineering (BME)</option>
                                <option value="MTE">Mechatronics Engineering (MTE)</option>
                                <option value="ESE">Energy Science & Engineering (ESE)</option>
                                <option value="ECE">Electronics & Communication Engineering (ECE)</option>
                                <option value="Chem">Chemical Engineering (Chem)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" placeholder="Your message..." required></textarea>
                        </div>
                        <button type="submit" class="contact-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Join Now Form Modal -->
        <div class="joinnow-modal" id="joinnow-modal">
            <div class="joinnow-modal-overlay" onclick="document.getElementById('joinnow-modal').style.display='none'"></div>
            <div class="joinnow-modal-content">
                <button class="joinnow-modal-close" onclick="document.getElementById('joinnow-modal').style.display='none'">&times;</button>
                <div class="joinnow-modal-header">
                    <h2>Join Now</h2>
                    <p>Fill out this form to connect with the Sylhet Association of KUET.</p>
                </div>
                <form class="joinnow-form" action="process/submit_join.php" method="POST" enctype="multipart/form-data">
                    <div class="joinnow-grid">
                        <div class="form-group">
                            <label for="join-name">Name</label>
                            <input type="text" id="join-name" name="name" placeholder="Your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="join-department">Department</label>
                            <select id="join-department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="CSE">Computer Science & Engineering (CSE)</option>
                                <option value="ME">Mechanical Engineering (ME)</option>
                                <option value="CE">Civil Engineering (CE)</option>
                                <option value="EEE">Electrical & Electronic Engineering (EEE)</option>
                                <option value="BECM">Bio-Medical Engineering (BECM)</option>
                                <option value="IPE">Industrial & Production Engineering (IPE)</option>
                                <option value="URP">Urban & Regional Planning (URP)</option>
                                <option value="TE">Textile Engineering (TE)</option>
                                <option value="LE">Leather Engineering (LE)</option>
                                <option value="MSE">Materials Science & Engineering (MSE)</option>
                                <option value="Arch">Architecture (Arch)</option>
                                <option value="BME">Biomedical Engineering (BME)</option>
                                <option value="MTE">Mechatronics Engineering (MTE)</option>
                                <option value="ESE">Energy Science & Engineering (ESE)</option>
                                <option value="ECE">Electronics & Communication Engineering (ECE)</option>
                                <option value="Chem">Chemical Engineering (Chem)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="join-batch">Batch</label>
                            <input type="text" id="join-batch" name="batch" placeholder="Example: 2K23" required>
                        </div>
                        <div class="form-group">
                            <label for="join-roll">Roll (Student ID)</label>
                            <input type="text" id="join-roll" name="roll" placeholder="Your roll number" required>
                        </div>
                        <div class="form-group">
                            <label for="join-phone">Phone Number</label>
                            <input type="tel" id="join-phone" name="phone" placeholder="Your contact phone" required>
                        </div>
                        <div class="form-group">
                            <label for="join-email">Email Address</label>
                            <input type="email" id="join-email" name="email" placeholder="Your email address" required>
                        </div>
                        <div class="form-group">
                            <label for="join-district">District</label>
                            <input type="text" id="join-district" name="district" placeholder="Your district" required>
                        </div>
                        <div class="form-group">
                            <label for="join-photo">Profile Photo (JPG/PNG - Max 5MB)</label>
                            <input type="file" id="join-photo" name="photo" accept="image/*" required>
                        </div>
                        <div class="form-group form-group--full">
                            <label for="join-connect">How you connect with Sylhet</label>
                            <textarea id="join-connect" name="connect" rows="4" placeholder="Tell us how you are connected with Sylhet" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="join-school">School</label>
                            <input type="text" id="join-school" name="school" placeholder="Your school name" required>
                        </div>
                        <div class="form-group">
                            <label for="join-college">College</label>
                            <input type="text" id="join-college" name="college" placeholder="Your college name" required>
                        </div>
                        <div class="form-group form-group--full">
                            <label for="join-expect">What you expect</label>
                            <textarea id="join-expect" name="expect" rows="4" placeholder="What do you expect from the association?" required></textarea>
                        </div>
                        <div class="form-group form-group--full">
                            <label for="join-memory">A memory with Sylhet</label>
                            <textarea id="join-memory" name="memory" rows="4" placeholder="Share a memory related to Sylhet" required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="joinnow-btn">Submit Application</button>
                </form>
            </div>
        </div>

        <!-- Donation Modal -->
        <div class="joinnow-modal" id="donation-modal" style="display: none;">
            <div class="joinnow-modal-overlay" onclick="document.getElementById('donation-modal').style.display='none'"></div>
            <div class="joinnow-modal-content" style="max-width: 500px;">
                <button class="joinnow-modal-close" onclick="document.getElementById('donation-modal').style.display='none'">&times;</button>
                <div class="joinnow-modal-header">
                    <h2>Submit Donation Details</h2>
                    <p>Send your donation through dynamic bank or mobile channel, then log your transaction receipt details below.</p>
                </div>
                <form class="joinnow-form" action="process/submit_donation.php" method="POST">
                    <input type="hidden" name="campaign_id" id="donate-campaign-id">
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>Target Campaign</label>
                        <input type="text" id="donate-campaign-title" readonly style="background: #e9ecef; border-color: #ced4da; cursor: not-allowed; font-weight: bold; color: #495057;">
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="donate-name">Donor Name / Organization</label>
                        <input type="text" id="donate-name" name="donor_name" placeholder="Your Name or Anonymous" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="donate-amount">Amount (৳)</label>
                        <input type="number" id="donate-amount" name="amount" min="1" step="0.01" placeholder="e.g. 500" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="donate-txid">Transaction ID (TrxID)</label>
                        <input type="text" id="donate-txid" name="transaction_id" placeholder="Mobile banking/Bank Ref transaction id" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 18px;">
                        <label for="donate-phone">Your Contact Phone</label>
                        <input type="tel" id="donate-phone" name="phone" placeholder="Enter contact phone">
                    </div>

                    <button type="submit" class="joinnow-btn">Submit Record</button>
                </form>
            </div>
        </div>

        <footer class="site-footer">
            <div class="site-footer__inner">
                <div class="site-footer__grid">
                    <div class="site-footer__column site-footer__brand">
                        <h3>Sylhet Association of KUET</h3>
                        <p>We are a community of Sylheti students at KUET, connected by culture, friendship, and support.</p>
                    </div>

                    <div class="site-footer__column">
                        <h4>Quick Links</h4>
                        <nav class="site-footer__nav" aria-label="Footer navigation">
                            <a href="#Home">Home</a>
                            <a href="#About">About</a>
                            <a href="#Events">Events</a>
                            <a href="#Gallery">Gallery</a>
                            <a href="#Members">Members</a>
                            <a href="#Notices">Notices</a>
                            <a href="#Funding">Funding</a>
                            <a href="#Jobs">Jobs</a>
                            <a href="#Emergency">Emergency</a>
                            <a href="#Contact">Contact</a>
                        </nav>
                    </div>

                    <div class="site-footer__column">
                        <h4>Contact</h4>
                        <p><a href="mailto:sylhetassoc.kuet@gmail.com">sylhetassoc.kuet@gmail.com</a></p>
                        <p><a href="https://facebook.com/groups/sylhetassociationkuet" target="_blank">Facebook Community</a></p>
                        <p>Khulna University of Engineering &amp; Technology</p>
                    </div>
                </div>

                <div class="site-footer__bottom">
                    <p class="site-footer__copy">&copy; 2026 Sylhet Association of KUET. All rights reserved. | <a href="admin/login.php" style="color: #f1c40f; text-decoration: underline;">Admin Area</a></p>
                </div>
            </div>
        </footer>
    </main>

    <!-- Notification Panel Drawer -->
    <div class="notif-panel" id="notif-panel">
        <div class="notif-header">
            <span>Notifications</span>
            <button class="notif-close-btn" onclick="toggleNotifPanel(event)">&times;</button>
        </div>
        <div class="notif-body" id="notif-body">
            <!-- Notifications will be dynamically loaded here by JS -->
        </div>
        <div class="notif-footer">
            <button class="notif-btn-clear" onclick="markAllNotificationsAsRead()">Mark all as read</button>
        </div>
    </div>

    <script>
        // Dynamic Notification System Data
        const dbNotifications = <?php echo json_encode($latest_notifications); ?>;

        function toggleNotifPanel(event) {
            if (event) event.stopPropagation();
            const panel = document.getElementById('notif-panel');
            panel.classList.toggle('open');
        }

        // Close panel when clicking outside
        document.addEventListener('click', function(event) {
            const panel = document.getElementById('notif-panel');
            const bell = document.getElementById('notif-bell-trigger');
            if (panel && panel.classList.contains('open')) {
                if (!panel.contains(event.target) && !bell.contains(event.target)) {
                    panel.classList.remove('open');
                }
            }
        });

        // Format time difference
        function formatTimeAgo(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr.replace(/-/g, "/"));
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMins / 60);
            const diffDays = Math.floor(diffHours / 24);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return diffMins + 'm ago';
            if (diffHours < 24) return diffHours + 'h ago';
            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return diffDays + 'd ago';
            
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        // Load notifications
        function loadNotifications() {
            const container = document.getElementById('notif-body');
            const badge = document.getElementById('notif-badge');
            const bellContainer = document.getElementById('notif-bell-trigger');
            
            if (!container) return 0;

            let readNotifs = [];
            try {
                readNotifs = JSON.parse(localStorage.getItem('sylhet_read_notifs')) || [];
            } catch (e) {
                readNotifs = [];
            }

            container.innerHTML = '';

            // Filter for ONLY UNREAD notifications!
            const unreadNotifications = dbNotifications.filter(notif => {
                const notifKey = notif.type + '_' + notif.id;
                return !readNotifs.includes(notifKey);
            });

            const unreadCount = unreadNotifications.length;

            if (unreadCount === 0) {
                container.innerHTML = `
                    <div style="padding: 30px 15px; text-align: center; color: #888;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px; color: #ccc; display: block; margin-left: auto; margin-right: auto;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <p style="margin: 0; font-size: 0.9rem;">No new notifications</p>
                    </div>
                `;
                if (badge) badge.style.display = 'none';
                if (bellContainer) bellContainer.classList.remove('has-unread');
                return 0;
            }

            unreadNotifications.forEach(notif => {
                const notifKey = notif.type + '_' + notif.id;
                const notifItem = document.createElement('div');
                notifItem.className = `notif-item notif-type-${notif.type} unread`;
                
                let targetHref = '#';
                if (notif.type === 'notice') targetHref = '#Notices';
                else if (notif.type === 'funding') targetHref = '#Funding';
                else if (notif.type === 'emergency') targetHref = '#Emergency';
                else if (notif.type === 'job') targetHref = '#Jobs';

                notifItem.innerHTML = `
                    <div class="notif-indicator"></div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%; gap: 10px;">
                        <span class="notif-title" style="flex: 1; text-align: left; font-weight: 700;">${escapeHtml(notif.title)}</span>
                        <span class="notif-type type-${notif.type}" style="font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; border: 1px solid currentColor; white-space: nowrap; font-weight: 800;">${notif.type}</span>
                    </div>
                    <span class="notif-time" style="align-self: flex-start; margin-top: 4px;">${formatTimeAgo(notif.created_at)}</span>
                `;

                notifItem.addEventListener('click', function(e) {
                    markAsRead(notifKey);
                    const panel = document.getElementById('notif-panel');
                    if (panel) panel.classList.remove('open');
                    
                    if (targetHref !== '#') {
                        setTimeout(() => {
                            const targetElement = document.querySelector(targetHref);
                            if (targetElement) {
                                targetElement.scrollIntoView({ behavior: 'smooth' });
                            }
                        }, 100);
                    }
                });

                container.appendChild(notifItem);
            });

            if (badge) {
                badge.innerText = unreadCount;
                badge.style.display = 'flex';
                if (bellContainer) bellContainer.classList.add('has-unread');
            }

            return unreadCount;
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function markAsRead(key) {
            let readNotifs = [];
            try {
                readNotifs = JSON.parse(localStorage.getItem('sylhet_read_notifs')) || [];
            } catch (e) {
                readNotifs = [];
            }

            if (!readNotifs.includes(key)) {
                readNotifs.push(key);
                localStorage.setItem('sylhet_read_notifs', JSON.stringify(readNotifs));
                loadNotifications();
            }
        }

        function markAllNotificationsAsRead() {
            if (!dbNotifications) return;
            const allKeys = dbNotifications.map(notif => notif.type + '_' + notif.id);
            localStorage.setItem('sylhet_read_notifs', JSON.stringify(allKeys));
            loadNotifications();
            
            setTimeout(() => {
                const panel = document.getElementById('notif-panel');
                if (panel) panel.classList.remove('open');
            }, 300);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const unreadCount = loadNotifications();
            if (unreadCount > 0 && !sessionStorage.getItem('sylhet_notif_opened')) {
                setTimeout(() => {
                    const panel = document.getElementById('notif-panel');
                    if (panel) {
                        panel.classList.add('open');
                        sessionStorage.setItem('sylhet_notif_opened', 'true');
                    }
                }, 1500);
            }
        });
        // Donation Modal control
        function openDonationModal(campaignId, campaignTitle) {
            document.getElementById('donate-campaign-id').value = campaignId;
            document.getElementById('donate-campaign-title').value = campaignTitle;
            document.getElementById('donation-modal').style.display = 'flex';
        }

        // Mobile nav toggle
        document.addEventListener('DOMContentLoaded', function(){
            var navToggle = document.getElementById('nav-toggle');
            var nav = document.getElementById('site-nav');
            navToggle && navToggle.addEventListener('click', function(){
                var expanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', String(!expanded));
                nav.classList.toggle('open');
            });
            nav && nav.addEventListener('click', function(event){
                if (event.target.tagName === 'A' && window.innerWidth <= 900) {
                    nav.classList.remove('open');
                    navToggle && navToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    </script>
</body>
</html>

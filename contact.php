<?php
// contact.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    $email_subject = "Contact Form: " . $subject;
    $email_message = "
        <h3>New Contact Form Submission</h3>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong></p>
        <p>$message</p>
    ";
    
    if (sendEmail(SITE_EMAIL, $email_subject, $email_message)) {
        setMessage('Thank you! Your message has been sent successfully.', 'success');
    } else {
        setMessage('Sorry, there was an error sending your message. Please try again.', 'error');
    }
    
    redirect('/contact');
}

$page_title = 'Contact Us - Girls Trip';

include 'includes/header.php';
?>

<section class="bg-pink-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Contact Us</h1>
        <p class="text-pink-100">Get in touch with our team</p>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                        <input type="text" name="name" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Your Email</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <input type="text" name="subject" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea name="message" rows="5" required 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"></textarea>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-pink-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Get in Touch</h2>
                
                <div class="space-y-6 mb-8">
                    <div class="flex items-start">
                        <div class="bg-pink-100 p-3 rounded-full mr-4">
                            <i class="fas fa-map-marker-alt text-pink-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Address</h3>
                            <p class="text-gray-700">Nairobi, Kenya</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-pink-100 p-3 rounded-full mr-4">
                            <i class="fas fa-phone text-pink-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Phone</h3>
                            <p class="text-gray-700">+254 700 000 000</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-pink-100 p-3 rounded-full mr-4">
                            <i class="fas fa-envelope text-pink-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Email</h3>
                            <p class="text-gray-700">info@girlstrip.co.ke</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-pink-100 p-3 rounded-full mr-4">
                            <i class="fas fa-clock text-pink-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Working Hours</h3>
                            <p class="text-gray-700">Monday - Saturday: 8am - 6pm</p>
                            <p class="text-gray-700">Sunday: Closed</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-pink-50 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Follow Us</h3>
                    <div class="flex gap-4">
                        <a href="#" class="bg-pink-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-pink-700 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-pink-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-pink-700 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="bg-pink-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-pink-700 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bg-pink-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-pink-700 transition">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<!-- END contact.php -->

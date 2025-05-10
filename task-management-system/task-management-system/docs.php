<?php
require_once "config/database.php";
require_once "includes/functions.php";

include "includes/header.php";
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Task Management System Documentation</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3>User Documentation</h3>
        <p>Welcome to the Task Management System documentation. Here you'll find comprehensive guides and resources to help you use the system effectively.</p>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4><i class="bi bi-book"></i> User Manual</h4>
                        <p>Complete guide to using the Task Management System, including step-by-step instructions for all features.</p>
                        <a href="docs/user-manual.md" class="btn btn-primary" target="_blank">View User Manual</a>
                        <a href="docs/index.html" class="btn btn-outline-primary ms-2" target="_blank">Interactive Guide</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4><i class="bi bi-question-circle"></i> Frequently Asked Questions</h4>
                        <p>Find answers to common questions about using the Task Management System.</p>
                        <a href="#" class="btn btn-primary">View FAQs</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4><i class="bi bi-play-btn"></i> Video Tutorials</h4>
                        <p>Watch video demonstrations of key features and workflows.</p>
                        <a href="#" class="btn btn-primary">Watch Tutorials</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4><i class="bi bi-tools"></i> Troubleshooting</h4>
                        <p>Solutions to common issues you might encounter while using the system.</p>
                        <a href="#" class="btn btn-primary">View Troubleshooting Guide</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <h5><i class="bi bi-info-circle"></i> Need Help?</h5>
            <p>If you can't find the information you need in our documentation, please contact our support team:</p>
            <ul>
                <li>Email: supportt@taskmanagement.com</li>
                <li>Phone: 1-800-TASKS</li>
                <li>Hours: Monday-Friday, 9 AM - 5 PM EST</li>
            </ul>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

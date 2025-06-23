<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="../dashboard/index.php" class="brand-link">
        <!-- <img src="../dist/img/mailSender.png" alt="MailSender Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
         
        <span class="brand-text font-weight-light">MailSender</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="../dashboard/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../mail/history.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'active' : ''; ?>">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Email History</p>
                    </a>
                </li>

                <!-- Email Management -->
                <!-- <li class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'mail') !== false) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'mail') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-envelope"></i>
                        <p>
                            Email Management
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="../mail/send.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'send.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Send Email</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../mail/history.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Email History</p>
                            </a>
                        </li>
                    </ul>
                </li> -->

                <?php if ($auth->canAccessCountryMenu('Australia')): ?>
                <li class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'austrailia') !== false) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'austrailia') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-envelope"></i>
                        <p>
                            Australia Emails
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if ($auth->canAccessCategoryMenu('File Submission')): ?>
                        <li class="nav-item">
                            <a href="../austrailia/file_submission.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'file_submission.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>File Submission</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($auth->canAccessCategoryMenu('Payment Received')): ?>
                        <li class="nav-item">
                            <a href="../austrailia/payment_receive.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'payment_receive.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Payment Receive</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($auth->canAccessCategoryMenu('Job Offer letter')): ?>
                        <li class="nav-item">
                            <a href="../austrailia/job_offer.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'job_offer.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Job Offer Letter</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($auth->canAccessCategoryMenu('Required Documents')): ?>
                        <li class="nav-item">
                            <a href="../austrailia/required_documents.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'required_documents.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Required documents</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($auth->canAccessCategoryMenu('Normal Rejection')): ?>
                        <li class="nav-item">
                            <a href="../austrailia/normal_rejection.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'normal_rejection.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Normal Rejection</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($auth->canAccessCategoryMenu('Notary Error Rejection')): ?>
                        <li class="nav-item">
                            <a href="../austrailia/notary_error_rejection.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'notary_error_rejection.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Notarry Error Rejection</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($auth->canAccessCategoryMenu('Company Rejection')): ?>
                        <li class="nav-item">
                            <a href="../austrailia/company_rejection.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'company_rejection.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Company Rejection</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>



                <!-- Email Templates -->
                <li class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'templates') !== false) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'templates') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Email Templates
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="../templates/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'templates') !== false) ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Templates</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../templates/create.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'create.php' && strpos($_SERVER['REQUEST_URI'], 'templates') !== false) ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Create Template</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Email Configuration (Admin/Super Admin only) -->
                <?php if ($auth->hasRole(['super_admin', 'admin'])): ?>
                <li class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'email-config') !== false) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'email-config') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            Email Configuration
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="../email-config/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'email-config') !== false) ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Configurations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../email-config/create.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'create.php' && strpos($_SERVER['REQUEST_URI'], 'email-config') !== false) ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add Configuration</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- User Management (Admin/Super Admin only) -->
                <?php if ($auth->hasRole(['super_admin', 'admin'])): ?>
                <li class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'users') !== false) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'users') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            User Management
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="../users/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'users') !== false) ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../users/create.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'create.php' && strpos($_SERVER['REQUEST_URI'], 'users') !== false) ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add User</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Profile -->
                <li class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'profile') !== false) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'profile') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                            Profile
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="../profile/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'profile') !== false) ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>View Profile</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../profile/settings.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Settings</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Logout -->
                <li class="nav-item">
                    <a href="../auth/logout.php" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
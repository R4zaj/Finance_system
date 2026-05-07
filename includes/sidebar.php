<!-- includes/sidebar.php -->
<style>
    :root {
        --brand-dark: #114227;
        --brand-hover: rgba(255, 255, 255, 0.1);
        --brand-active: rgba(255, 255, 255, 0.2);
    }
    
    /* Sidebar Styling */
    #sidebar {
        background-color: var(--brand-dark);
        width: 260px;
        min-width: 260px;
        z-index: 1040;
        transition: transform 0.3s ease-in-out;
    }
    
    .sidebar-heading {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1px;
        color: rgba(255, 255, 255, 0.5);
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        padding-left: 1rem;
    }
    
    .sidebar-link {
        color: rgba(255, 255, 255, 0.8);
        border-radius: 0.5rem;
        padding: 0.6rem 1rem;
        margin-bottom: 0.25rem;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        font-weight: 500;
        display: flex;
        align-items: center;
    }
    
    .sidebar-link:hover {
        background-color: var(--brand-hover);
        color: white;
        transform: translateX(4px);
    }
    
    .sidebar-link.active {
        background-color: var(--brand-active);
        color: white;
        font-weight: 600;
    }
    
    .sidebar-link i {
        width: 24px;
        text-align: center;
        margin-right: 12px;
        font-size: 1.1rem;
    }

    /* Mobile offcanvas behavior */
    @media (max-width: 991.98px) {
        #sidebar {
            position: fixed;
            height: 100vh;
            transform: translateX(-100%);
        }
        #sidebar.show {
            transform: translateX(0);
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1030;
        }
        .sidebar-overlay.show {
            display: block;
        }
    }
</style>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<nav id="sidebar" class="vh-100 d-flex flex-column p-3 overflow-y-auto">
    
    <!-- Close button for mobile -->
    <div class="d-lg-none text-end mb-2">
        <button class="btn btn-link text-white-50 p-0" id="closeSidebar">
            <i class="bi bi-x-lg fs-4"></i>
        </button>
    </div>

    <!-- MAIN MENU -->
    <div class="sidebar-heading text-uppercase">Main Menu</div>
    <ul class="nav flex-column mb-auto" id="nav-links">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link sidebar-link text-decoration-none">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
    </ul>

    <!-- FINANCIAL MANAGEMENT -->
    <div class="sidebar-heading text-uppercase">Financial Management</div>
    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a href="accounting.php" class="nav-link sidebar-link text-decoration-none">
                <i class="bi bi-journal-text"></i> Chart of Accounts
            </a>
        </li>
        <li class="nav-item">
            <a href="budgeting.php" class="nav-link sidebar-link text-decoration-none">
                <i class="bi bi-pie-chart"></i> Budget Management
            </a>
        </li>
    </ul>

    <!-- PAYMENTS -->
    <div class="sidebar-heading text-uppercase">Payments</div>
    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a href="tuition.php" class="nav-link sidebar-link text-decoration-none">
                <i class="bi bi-cash-coin"></i> Student Payments
            </a>
        </li>
    </ul>

    <!-- PROCUREMENT -->
    <div class="sidebar-heading text-uppercase">Procurement</div>
    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a href="procurement.php" class="nav-link sidebar-link text-decoration-none">
                <i class="bi bi-cart3"></i> Purchase Orders
            </a>
        </li>
    </ul>

    <!-- HR & PAYROLL -->
    <div class="sidebar-heading text-uppercase">HR & Payroll</div>
    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a href="payroll.php" class="nav-link sidebar-link text-decoration-none">
                <i class="bi bi-person-vcard"></i> HR Payroll
            </a>
        </li>
    </ul>

    <!-- REPORTS -->
    <div class="sidebar-heading text-uppercase">Reports</div>
    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a href="financial_reports.php" class="nav-link sidebar-link text-decoration-none">
                <i class="bi bi-bar-chart-line"></i> Financial Reports
            </a>
        </li>
    </ul>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Active Tab Logic
        const currentLocation = location.pathname.split("/").pop();
        const navLinks = document.querySelectorAll(".sidebar-link");
        navLinks.forEach(link => {
            if (link.getAttribute("href") === currentLocation) {
                link.classList.add("active");
            }
        });

        // Mobile Sidebar Toggle Logic
        const toggleBtn = document.getElementById('toggleSidebar'); // Button in topheader
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const closeBtn = document.getElementById('closeSidebar');

        function toggleMenu() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        if(toggleBtn) toggleBtn.addEventListener('click', toggleMenu);
        if(closeBtn) closeBtn.addEventListener('click', toggleMenu);
        if(overlay) overlay.addEventListener('click', toggleMenu);
    });
</script>

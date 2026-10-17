<?php
require_once "db.php";

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$successMessage = "";
$errorMessage = "";

if (isset($_GET["status"])) {
    $messages = [
        "added" => "Application added successfully!",
        "updated" => "Application updated successfully!",
        "deleted" => "Application deleted successfully!"
    ];

    $successMessage = $messages[$_GET["status"]] ?? "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST["first_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contactNumber = trim($_POST["contact_number"] ?? "");
    $position = trim($_POST["position_applied"] ?? "");
    $yearsExperience = filter_var(
        $_POST["years_experience"] ?? null,
        FILTER_VALIDATE_INT
    );
    $portfolioUrl = trim($_POST["portfolio_url"] ?? "");
    $skills = trim($_POST["skills"] ?? "");

    $allowedPositions = [
        "UI/UX Designer",
        "Junior UI/UX Designer",
        "Product Designer",
        "UI Designer"
    ];

    if (
        $firstName === "" ||
        $lastName === "" ||
        $email === "" ||
        $contactNumber === "" ||
        $position === "" ||
        $yearsExperience === false ||
        $yearsExperience === null ||
        $yearsExperience < 0 ||
        $skills === ""
    ) {
        $errorMessage = "Please complete all required fields correctly.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address.";
    } elseif (!in_array($position, $allowedPositions, true)) {
        $errorMessage = "Please select a valid position.";
    } elseif (
        $portfolioUrl !== "" &&
        (
            !filter_var($portfolioUrl, FILTER_VALIDATE_URL) ||
            !in_array(
                strtolower((string) parse_url($portfolioUrl, PHP_URL_SCHEME)),
                ["http", "https"],
                true
            )
        )
    ) {
        $errorMessage = "Please enter a valid portfolio URL.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO applications
                (
                    first_name,
                    last_name,
                    email,
                    contact_number,
                    position_applied,
                    years_experience,
                    portfolio_url,
                    skills
                )
                VALUES
                (
                    :first_name,
                    :last_name,
                    :email,
                    :contact_number,
                    :position_applied,
                    :years_experience,
                    :portfolio_url,
                    :skills
                )"
            );

            $stmt->execute([
                ":first_name" => $firstName,
                ":last_name" => $lastName,
                ":email" => $email,
                ":contact_number" => $contactNumber,
                ":position_applied" => $position,
                ":years_experience" => $yearsExperience,
                ":portfolio_url" => $portfolioUrl !== ""
                    ? $portfolioUrl
                    : null,
                ":skills" => $skills
            ]);

            header("Location: index.php?status=added");
            exit;
        } catch (PDOException $e) {
            $errorMessage = "Unable to save the application. Please try again.";
        }
    }
}

$stmt = $pdo->query(
    "SELECT * FROM applications ORDER BY application_id DESC"
);

$applications = $stmt->fetchAll();

$totalApplications = count($applications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DesignDesk | UI/UX Job Applications</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
    <div class="container">
        <a href="index.php" class="logo">DesignDesk.</a>

        <nav>
            <a href="#application-form">Apply Now</a>
            <a href="#applications">Applications</a>
        </nav>
    </div>
</header>

<main class="container">

    <!-- Hero Section -->
    <section class="hero">
        <span class="eyebrow">CAREER OPPORTUNITIES</span>

        <h1>Design your future.</h1>

        <p>
            Join our creative team. Submit your application for a UI/UX
            design position and showcase your skills, experience, and portfolio.
        </p>
    </section>

    <!-- Success Message -->
    <?php if ($successMessage !== ""): ?>
        <div class="alert alert-success">
            <?php echo e($successMessage); ?>
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if ($errorMessage !== ""): ?>
        <div class="alert alert-danger">
            <?php echo e($errorMessage); ?>
        </div>
    <?php endif; ?>

    <!-- Application Form -->
    <section class="form-card" id="application-form">

        <div class="section-header">
            <div>
                <span class="section-label">APPLICATION FORM</span>
                <h2>Apply for a position</h2>
                <p>Fields marked with * are required.</p>
            </div>
        </div>

        <form action="index.php" method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label for="first_name">First Name *</label>

                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        placeholder="Enter your first name"
                        maxlength="100"
                        required
                        value="<?php echo e($_POST['first_name'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name *</label>

                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        placeholder="Enter your last name"
                        maxlength="100"
                        required
                        value="<?php echo e($_POST['last_name'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        maxlength="150"
                        required
                        value="<?php echo e($_POST['email'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number *</label>

                    <input
                        type="tel"
                        id="contact_number"
                        name="contact_number"
                        placeholder="09XX XXX XXXX"
                        maxlength="30"
                        required
                        value="<?php echo e($_POST['contact_number'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="position_applied">Position Applied For *</label>

                    <select
                        id="position_applied"
                        name="position_applied"
                        required
                    >
                        <option value="">Select a position</option>

                        <?php
                        $positions = [
                            "UI/UX Designer",
                            "Junior UI/UX Designer",
                            "Product Designer",
                            "UI Designer"
                        ];
                        ?>

                        <?php foreach ($positions as $item): ?>
                            <option
                                value="<?php echo e($item); ?>"
                                <?php echo (
                                    ($_POST["position_applied"] ?? "") === $item
                                ) ? "selected" : ""; ?>
                            >
                                <?php echo e($item); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="years_experience">
                        Years of Experience *
                    </label>

                    <input
                        type="number"
                        id="years_experience"
                        name="years_experience"
                        min="0"
                        max="60"
                        placeholder="e.g. 1"
                        required
                        value="<?php echo e($_POST['years_experience'] ?? '0'); ?>"
                    >
                </div>

                <div class="form-group full-width">
                    <label for="portfolio_url">Portfolio URL</label>

                    <input
                        type="url"
                        id="portfolio_url"
                        name="portfolio_url"
                        placeholder="https://yourportfolio.com"
                        maxlength="255"
                        value="<?php echo e($_POST['portfolio_url'] ?? ''); ?>"
                    >

                    <span class="form-help">
                        Optional. Add your portfolio or design showcase link.
                    </span>
                </div>

                <div class="form-group full-width">
                    <label for="skills">Skills and Tools *</label>

                    <textarea
                        id="skills"
                        name="skills"
                        placeholder="e.g. Figma, wireframing, prototyping, user research"
                        required
                    ><?php echo e($_POST['skills'] ?? ''); ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit">
                    Submit Application &rarr;
                </button>

                <button
                    type="reset"
                    class="btn-secondary"
                >
                    Clear Form
                </button>
            </div>

        </form>
    </section>

    <!-- Applications Table -->
    <section class="table-card" id="applications">

        <div class="table-header">
            <div>
                <span class="section-label">APPLICATION MANAGEMENT</span>
                <h2>Submitted Applications</h2>

                <p>
                    Review and manage submitted job applications.
                </p>
            </div>

            <span class="badge">
                <?php echo $totalApplications; ?>
                <?php echo $totalApplications === 1
                    ? "Application"
                    : "Applications"; ?>
            </span>
        </div>

        <?php if ($totalApplications > 0): ?>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Position</th>
                            <th>Experience</th>
                            <th>Portfolio</th>
                            <th>Skills</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($applications as $application): ?>
                            <tr>
                                <td>
                                    <strong>
                                        #<?php echo e($application["application_id"]); ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong>
                                        <?php
                                        echo e(
                                            $application["first_name"] . " " .
                                            $application["last_name"]
                                        );
                                        ?>
                                    </strong>
                                </td>

                                <td><?php echo e($application["email"]); ?></td>

                                <td>
                                    <?php echo e($application["contact_number"]); ?>
                                </td>

                                <td>
                                    <span class="badge">
                                        <?php echo e($application["position_applied"]); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    echo e($application["years_experience"]);
                                    ?>
                                    <?php
                                    echo (int) $application["years_experience"] === 1
                                        ? "year"
                                        : "years";
                                    ?>
                                </td>

                                <td>
                                    <?php if (!empty($application["portfolio_url"])): ?>
                                        <a
                                            href="<?php echo e($application["portfolio_url"]); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            View Portfolio
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php echo e($application["skills"]); ?>
                                </td>

                                <td>
                                    <?php
                                    echo e(
                                        date(
                                            "M d, Y",
                                            strtotime($application["date_added"])
                                        )
                                    );
                                    ?>
                                </td>

                                <td>
                                    <div class="actions">
                                        <a
                                            href="edit.php?id=<?php
                                                echo (int) $application["application_id"];
                                            ?>"
                                            class="btn btn-small"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="delete.php"
                                            method="POST"
                                            onsubmit="return confirm(
                                                'Are you sure you want to delete this application?'
                                            );"
                                        >
                                            <input
                                                type="hidden"
                                                name="application_id"
                                                value="<?php
                                                    echo (int) $application["application_id"];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-danger btn-small"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="empty-state">
                <h3>No applications yet</h3>

                <p>
                    Your submitted applications will appear here.
                    Fill out the form above to get started.
                </p>

                <a href="#application-form" class="btn">
                    Add First Application
                </a>
            </div>

        <?php endif; ?>

    </section>

</main>

<footer>
    <div class="container">
        <p>
            &copy; <?php echo date("Y"); ?> DesignDesk.
            Job Application Management System.
        </p>
    </div>
</footer>

</body>
</html>
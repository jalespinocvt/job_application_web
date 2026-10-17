<?php
require_once "db.php";

function e($value) {
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT * FROM applications WHERE application_id = ?"
);
$stmt->execute([$id]);
$app = $stmt->fetch();

if (!$app) {
    header("Location: index.php");
    exit;
}

$error = "";

$positions = [
    "UI/UX Designer",
    "Junior UI/UX Designer",
    "Product Designer",
    "UI Designer"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST["first_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contact = trim($_POST["contact_number"] ?? "");
    $position = trim($_POST["position_applied"] ?? "");
    $experience = $_POST["years_experience"] ?? "";
    $portfolio = trim($_POST["portfolio_url"] ?? "");
    $skills = trim($_POST["skills"] ?? "");

    $validPortfolio = true;

    if ($portfolio !== "") {
        $scheme = strtolower(
            (string)parse_url($portfolio, PHP_URL_SCHEME)
        );

        $validPortfolio =
            filter_var($portfolio, FILTER_VALIDATE_URL) &&
            in_array($scheme, ["http", "https"], true);
    }

    if (
        $firstName === "" ||
        $lastName === "" ||
        $contact === "" ||
        $skills === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($position, $positions, true) ||
        !ctype_digit((string)$experience) ||
        (int)$experience > 80 ||
        strlen($firstName) > 100 ||
        strlen($lastName) > 100 ||
        strlen($email) > 150 ||
        strlen($contact) > 30 ||
        strlen($portfolio) > 255 ||
        !$validPortfolio
    ) {
        $error = "Please check your information and try again.";
    } else {
        $sql = "UPDATE applications SET
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    contact_number = :contact_number,
                    position_applied = :position_applied,
                    years_experience = :years_experience,
                    portfolio_url = :portfolio_url,
                    skills = :skills
                WHERE application_id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":first_name" => $firstName,
            ":last_name" => $lastName,
            ":email" => $email,
            ":contact_number" => $contact,
            ":position_applied" => $position,
            ":years_experience" => (int)$experience,
            ":portfolio_url" => $portfolio ?: null,
            ":skills" => $skills,
            ":id" => $id
        ]);

        header("Location: index.php?status=updated");
        exit;
    }

    $app["first_name"] = $firstName;
    $app["last_name"] = $lastName;
    $app["email"] = $email;
    $app["contact_number"] = $contact;
    $app["position_applied"] = $position;
    $app["years_experience"] = $experience;
    $app["portfolio_url"] = $portfolio;
    $app["skills"] = $skills;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Application | DesignDesk</title>

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-inner">
        <a href="index.php" class="brand">
            <span class="brand-mark">U</span>
            <span>Design<span class="brand-light">Desk</span></span>
        </a>

        <a href="index.php" class="back-link">← Back to applications</a>
    </div>
</header>

<main class="container edit-container">

    <section class="welcome">
        <div>
            <p class="eyebrow">APPLICATION MANAGEMENT</p>
            <h1>Edit Application</h1>
            <p class="welcome-text">
                Update the applicant's information below.
            </p>
        </div>

        <div class="record-badge">
            Record #<?= (int)$id ?>
        </div>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Applicant Details</h2>
                <p class="section-description">
                    Fields marked with * are required.
                </p>
            </div>
        </div>

        <?php if ($error !== ""): ?>
            <div class="notice error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form action="edit.php?id=<?= (int)$id ?>"
              method="POST"
              class="form-grid">

            <div class="field">
                <label for="first_name">First name <span>*</span></label>
                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    maxlength="100"
                    value="<?= e($app["first_name"]) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="last_name">Last name <span>*</span></label>
                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    maxlength="100"
                    value="<?= e($app["last_name"]) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="email">Email address <span>*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="150"
                    value="<?= e($app["email"]) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="contact_number">Contact number <span>*</span></label>
                <input
                    type="tel"
                    id="contact_number"
                    name="contact_number"
                    maxlength="30"
                    value="<?= e($app["contact_number"]) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="position_applied">Position applied for <span>*</span></label>
                <select id="position_applied" name="position_applied" required>
                    <?php foreach ($positions as $option): ?>
                        <option
                            value="<?= e($option) ?>"
                            <?= $app["position_applied"] === $option
                                ? "selected" : "" ?>
                        >
                            <?= e($option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="years_experience">Years of experience <span>*</span></label>
                <input
                    type="number"
                    id="years_experience"
                    name="years_experience"
                    min="0"
                    max="80"
                    step="1"
                    value="<?= e($app["years_experience"]) ?>"
                    required
                >
            </div>

            <div class="field full">
                <label for="portfolio_url">Portfolio URL</label>
                <input
                    type="url"
                    id="portfolio_url"
                    name="portfolio_url"
                    maxlength="255"
                    value="<?= e($app["portfolio_url"]) ?>"
                    placeholder="https://yourportfolio.com"
                >
            </div>

            <div class="field full">
                <label for="skills">Skills <span>*</span></label>
                <textarea
                    id="skills"
                    name="skills"
                    rows="4"
                    required
                ><?= e($app["skills"]) ?></textarea>
            </div>

            <div class="form-actions full">
                <a href="index.php" class="btn btn-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save changes →
                </button>
            </div>

        </form>
    </section>

    <footer>
        <span>DesignDesk</span> · UI/UX Designer Job Application System
    </footer>

</main>

</body>
</html>
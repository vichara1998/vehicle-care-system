<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_require_csrf_token();
}
$userId = app_require_authenticated_user();

$conn = app_db_connect();

// Handle question deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {
    $question_id = filter_var($_POST['question_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $user_id = $userId;

    if ($question_id === false) {
        http_response_code(400);
        exit('Invalid question.');
    }

    $question_owner = null;
    $stmt = $conn->prepare("SELECT user_id FROM questions WHERE question_id = ?");
    $stmt->bind_param('i', $question_id);
    $stmt->execute();
    $stmt->bind_result($question_owner);
    $stmt->fetch();
    $stmt->close();

    // Allow deletion only if the user is the question owner or an admin
    if ($question_owner !== null && ((int) $question_owner === $user_id || ($_SESSION['role'] ?? '') === 'admin')) {
        // Delete all related replies
        $stmt = $conn->prepare("DELETE FROM answers WHERE question_id = ?");
        $stmt->bind_param('i', $question_id);
        $stmt->execute();
        $stmt->close();

        // Delete the question
        $stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
        $stmt->bind_param('i', $question_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<script>alert('You are not authorized to delete this question.');</script>";
    }
}

// Handle new question submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $user_id = $userId;
    $question = trim($_POST['question'] ?? '');

    if ($question !== '' && strlen($question) <= 5000) {
        $stmt = $conn->prepare("INSERT INTO questions (user_id, question) VALUES (?, ?)");
        $stmt->bind_param('is', $user_id, $question);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<script>alert('Question cannot be empty.');</script>";
    }
}

// Handle new answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_answer'])) {
    $user_id = $userId;
    $question_id = filter_var($_POST['question_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $answer = trim($_POST['answer'] ?? '');

    if ($question_id !== false && $answer !== '' && strlen($answer) <= 5000) {
        $stmt = $conn->prepare("INSERT INTO answers (question_id, user_id, answer) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $question_id, $user_id, $answer);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<script>alert('Answer cannot be empty.');</script>";
    }
}

// Fetch all questions with their answers
$questions = [];
$result = $conn->query("SELECT q.question_id, q.question, q.created_at, q.user_id, u.name 
                        FROM questions q
                        JOIN users u ON q.user_id = u.user_id
                        ORDER BY q.created_at DESC");

while ($row = $result->fetch_assoc()) {
    $question_id = $row['question_id'];
    $answers = $conn->query("SELECT a.answer, a.created_at, u.name 
                             FROM answers a
                             JOIN users u ON a.user_id = u.user_id
                             WHERE a.question_id = $question_id
                             ORDER BY a.created_at ASC")->fetch_all(MYSQLI_ASSOC);
    $questions[] = ['question' => $row, 'answers' => $answers];
}
?>


<!DOCTYPE html>
<html>
<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Q&A Forum</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006l">
</head>
<body class="img-background q-a">
    <!-- Menu Bar -->
    <div class="menu-bar">
        <ul>
            <li><a href="pages/home.php">Home</a></li>
            <li><a href="pages/spareparts.php">Products</a></li>
            <li><a href="pages/user_details.php">Profile</a></li>
            <li><a href="pages/livesupport.php">Support</a></li>
            <li><form method="POST" action="auth/logout.php" class="logout-form" onsubmit="return confirm('Are you sure you want to logout?');"><input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><button type="submit" class="logout-button">Logout</button></form></li>
        </ul>
    </div>
    <div class="forum-container">
        <h1>Question and Answers Forum</h1>

        <!-- Add Discussion Form -->
        <div class="add-discussion card2">
            <h2>Add a New Discussion</h2>
            <form method="POST" onsubmit="disableSubmitButton(this)">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <textarea name="question" placeholder="Type your question here..." required></textarea>
            <button type="submit" name="add_question">Post Question</button>
        </form>

        </div>

        <!-- Display Questions and Answers -->
<div class="discussions">
    <?php foreach ($questions as $q): ?>
        <div class="discussion card2">
            <h3><?php echo htmlspecialchars($q['question']['question'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p class="question-meta">
                Posted by: <span class="user"><?php echo htmlspecialchars($q['question']['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                on <span class="date"><?php echo htmlspecialchars($q['question']['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
            </p>

            <!-- Delete Button (Only for Admin or Question Owner) -->
            <?php if ((int) $q['question']['user_id'] === $userId || ($_SESSION['role'] ?? '') === 'admin'): ?>
                <form method="POST" class="delete-question">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="question_id" value="<?= (int) $q['question']['question_id'] ?>">
                    <button type="submit" name="delete_question" class="delete-button">Delete</button>
                </form>
            <?php endif; ?>

            <!-- Display Answers -->
            <div class="answers">
                <?php if (!empty($q['answers'])): ?>
                    <h4>Answers:</h4>
                <?php endif; ?>
                <?php foreach ($q['answers'] as $a): ?>
                    <div class="answer">
                        <p><strong><?php echo htmlspecialchars($a['name'], ENT_QUOTES, 'UTF-8'); ?>:</strong>
                           <?php echo htmlspecialchars($a['answer'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <small>Posted on <?php echo htmlspecialchars($a['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Add Answer Form -->
            <form method="POST" class="add-answer">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <textarea name="answer" placeholder="Type your answer here..." required></textarea>
                <input type="hidden" name="question_id" value="<?= (int) $q['question']['question_id'] ?>">
                <button type="submit" name="add_answer">Reply</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

    </div>
</body>
</html>

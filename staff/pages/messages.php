<?php
// Handle message replies
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply') {
        $message_id = $_POST['message_id'];
        $reply = $_POST['reply'];
        
        $stmt = $conn->prepare("UPDATE messages SET reply = ?, status = 'replied', replied_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("si", $reply, $message_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Reply sent successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to send reply.";
        }
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Build query
$query = "SELECT m.*, u.name as customer_name 
          FROM messages m 
          JOIN user_accounts u ON m.customer_id = u.id 
          WHERE 1=1";

$params = [];
$types = "";

if ($search) {
    $search = "%$search%";
    $query .= " AND (u.name LIKE ? OR m.subject LIKE ? OR m.message LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

if ($status_filter) {
    $query .= " AND m.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY m.$sort $order";

// Execute search
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Message Management</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php 
            echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php 
            echo htmlspecialchars($_SESSION['error_message']);
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Search and Filter Form -->
    <form method="GET" action="index.php" class="flex gap-4 mb-6">
        <input type="hidden" name="page" value="messages">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Search messages..." 
               class="border rounded px-4 py-2 w-64 focus:border-primary focus:ring-primary">
               
        <select name="status" class="border rounded px-4 py-2 focus:border-primary focus:ring-primary">
            <option value="">All Status</option>
            <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>Unread</option>
            <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
        </select>
        
        <select name="sort" class="border rounded px-4 py-2 focus:border-primary focus:ring-primary">
            <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Date</option>
            <option value="status" <?php echo $sort === 'status' ? 'selected' : ''; ?>>Status</option>
            <option value="subject" <?php echo $sort === 'subject' ? 'selected' : ''; ?>>Subject</option>
            <option value="customer_name" <?php echo $sort === 'customer_name' ? 'selected' : ''; ?>>Customer Name</option>
        </select>
        
        <select name="order" class="border rounded px-4 py-2 focus:border-primary focus:ring-primary">
            <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Newest First</option>
            <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
        </select>
        
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
            Apply
        </button>
    </form>

    <!-- Messages List -->
    <div class="space-y-4">
        <?php while ($message = $result->fetch_assoc()): ?>
        <div class="border rounded-lg p-4 <?php echo $message['status'] === 'unread' ? 'bg-primary/5' : 'bg-white'; ?>">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-semibold text-primary"><?php echo htmlspecialchars($message['subject']); ?></h3>
                    <p class="text-sm text-gray-600">
                        From: <?php echo htmlspecialchars($message['customer_name']); ?> | 
                        <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                    </p>
                </div>
                <span class="px-2 py-1 rounded text-sm <?php echo $message['status'] === 'unread' ? 'bg-primary/10 text-primary' : 'bg-green-100 text-green-800'; ?>">
                    <?php echo ucfirst($message['status']); ?>
                </span>
            </div>
            
            <div class="text-gray-700 mb-4">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>
            
            <?php if ($message['reply']): ?>
            <div class="bg-gray-50 p-4 rounded mb-4">
                <p class="text-sm text-gray-600 mb-2">
                    Reply sent on <?php echo date('M d, Y H:i', strtotime($message['replied_at'])); ?>
                </p>
                <div class="text-gray-700">
                    <?php echo nl2br(htmlspecialchars($message['reply'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($message['status'] === 'unread'): ?>
            <button onclick="showReplyModal(<?php echo $message['id']; ?>, '<?php echo htmlspecialchars($message['subject']); ?>')"
                    class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
                Reply
            </button>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
        
        <?php if ($result->num_rows === 0): ?>
            <p class="text-gray-500 text-center py-4">No messages found with the current filters.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Reply Modal -->
<div id="replyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Reply to Message</h3>
                <button onclick="closeReplyModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="replyForm" method="POST">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="message_id" id="replyMessageId">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Subject</label>
                    <input type="text" id="replySubject" disabled
                           class="border rounded w-full px-3 py-2 bg-gray-100">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Your Reply</label>
                    <textarea name="reply" required rows="6"
                              class="border rounded w-full px-3 py-2"></textarea>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeReplyModal()"
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showReplyModal(messageId, subject) {
    document.getElementById('replyMessageId').value = messageId;
    document.getElementById('replySubject').value = subject;
    document.getElementById('replyModal').classList.remove('hidden');
}

function closeReplyModal() {
    document.getElementById('replyModal').classList.add('hidden');
}
</script>

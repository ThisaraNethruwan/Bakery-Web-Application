<?php
require_once('components/header.php');
require_once('../admin/db_connect.php');

// Handle message replies
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply'])) {
    $message_id = $_POST['message_id'];
    $reply = $_POST['reply'];
    $staff_id = $_SESSION['user_id'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert reply
        $sql = "INSERT INTO message_replies (message_id, staff_id, reply_text, reply_date) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $message_id, $staff_id, $reply);
        $stmt->execute();
        
        // Update message status
        $sql = "UPDATE messages SET status = 'replied' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
        
        $conn->commit();
        header("Location: messages.php?success=1");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error sending reply. Please try again.";
    }
}

require_once('components/sidebar.php');
?>

<!-- Main Content -->
<div class="flex-1 p-8">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Message Management</h2>
        </div>

        <!-- Messages List -->
        <div class="grid grid-cols-12 h-[calc(100vh-12rem)]">
            <!-- Messages Sidebar -->
            <div class="col-span-4 border-r border-gray-200 overflow-y-auto">
                <div class="p-4">
                    <div class="flex space-x-2 mb-4">
                        <button onclick="filterMessages('all')" 
                                class="flex-1 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 message-filter active">
                            All
                        </button>
                        <button onclick="filterMessages('unread')" 
                                class="flex-1 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 message-filter">
                            Unread
                        </button>
                        <button onclick="filterMessages('replied')" 
                                class="flex-1 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 message-filter">
                            Replied
                        </button>
                    </div>

                    <div id="messagesList" class="space-y-2">
                        <?php
                        $sql = "SELECT m.*, c.name as customer_name, 
                                (SELECT COUNT(*) FROM message_replies WHERE message_id = m.id) as reply_count
                                FROM messages m
                                JOIN customers c ON m.customer_id = c.id
                                ORDER BY m.message_date DESC";
                        $result = $conn->query($sql);
                        
                        if ($result && $result->num_rows > 0):
                            while ($message = $result->fetch_assoc()):
                        ?>
                            <div class="message-item p-4 rounded-lg cursor-pointer hover:bg-gray-50 transition-all"
                                 data-id="<?php echo $message['id']; ?>"
                                 data-status="<?php echo $message['status']; ?>"
                                 onclick="loadMessage(<?php echo htmlspecialchars(json_encode($message)); ?>)">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium"><?php echo htmlspecialchars($message['customer_name']); ?></h4>
                                    <div class="flex items-center space-x-2">
                                        <?php if ($message['status'] === 'unread'): ?>
                                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">New</span>
                                        <?php endif; ?>
                                        <?php if ($message['reply_count'] > 0): ?>
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                <?php echo $message['reply_count']; ?> <?php echo $message['reply_count'] === 1 ? 'Reply' : 'Replies'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 truncate"><?php echo htmlspecialchars($message['message']); ?></p>
                                <p class="text-xs text-gray-500 mt-2">
                                    <?php echo date('M d, Y H:i', strtotime($message['message_date'])); ?>
                                </p>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <div class="text-center text-gray-500 py-8">No messages found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Message Content -->
            <div class="col-span-8 flex flex-col">
                <div id="messageContent" class="p-6 flex-1 overflow-y-auto">
                    <div class="flex items-center justify-center h-full text-gray-500">
                        Select a message to view details
                    </div>
                </div>
                
                <!-- Reply Form -->
                <div id="replyForm" class="p-6 border-t border-gray-200 hidden">
                    <form action="" method="POST" class="space-y-4">
                        <input type="hidden" name="message_id" id="replyMessageId">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Your Reply</label>
                            <textarea name="reply" rows="3" required
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" name="send_reply"
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                                Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterMessages(status) {
    // Update filter button styles
    document.querySelectorAll('.message-filter').forEach(btn => {
        btn.classList.remove('active', 'bg-red-600', 'text-white');
        btn.classList.add('bg-gray-100');
    });
    event.target.classList.remove('bg-gray-100');
    event.target.classList.add('active', 'bg-red-600', 'text-white');
    
    // Filter messages
    document.querySelectorAll('.message-item').forEach(item => {
        if (status === 'all' || item.dataset.status === status) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function loadMessage(message) {
    const contentDiv = document.getElementById('messageContent');
    const replyForm = document.getElementById('replyForm');
    const replyMessageId = document.getElementById('replyMessageId');
    
    // Highlight selected message
    document.querySelectorAll('.message-item').forEach(item => {
        item.classList.remove('bg-gray-100');
    });
    document.querySelector(`.message-item[data-id="${message.id}"]`).classList.add('bg-gray-100');
    
    // Load message content and replies
    fetch(`get_message_details.php?id=${message.id}`)
        .then(response => response.json())
        .then(data => {
            contentDiv.innerHTML = `
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold">${message.customer_name}</h3>
                                <p class="text-sm text-gray-500">
                                    ${new Date(message.message_date).toLocaleString()}
                                </p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-sm 
                                ${message.status === 'unread' ? 'bg-red-100 text-red-800' : 
                                  message.status === 'replied' ? 'bg-green-100 text-green-800' : 
                                  'bg-gray-100 text-gray-800'}">
                                ${message.status.charAt(0).toUpperCase() + message.status.slice(1)}
                            </span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p>${message.message}</p>
                        </div>
                    </div>

                    ${data.replies.map(reply => `
                        <div class="ml-8">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium">Staff Reply</p>
                                    <p class="text-sm text-gray-500">
                                        ${new Date(reply.reply_date).toLocaleString()}
                                    </p>
                                </div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p>${reply.reply_text}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
            
            replyMessageId.value = message.id;
            replyForm.classList.remove('hidden');
        });
}

// Add active class to filter buttons
document.querySelectorAll('.message-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.message-filter').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<?php
$conn->close();
?>

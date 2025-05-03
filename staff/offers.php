<?php
require_once('components/header.php');
require_once('../admin/db_connect.php');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_offer'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $discount_type = $_POST['discount_type'];
        $discount_value = $_POST['discount_value'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $min_purchase = $_POST['min_purchase'];
        $code = $_POST['code'];
        
        $sql = "INSERT INTO offers (title, description, discount_type, discount_value, start_date, end_date, min_purchase, code) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdssds", $title, $description, $discount_type, $discount_value, $start_date, $end_date, $min_purchase, $code);
        
        if ($stmt->execute()) {
            header("Location: offers.php?success=1");
            exit;
        }
    }
    
    if (isset($_POST['edit_offer'])) {
        $id = $_POST['offer_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $discount_type = $_POST['discount_type'];
        $discount_value = $_POST['discount_value'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $min_purchase = $_POST['min_purchase'];
        $code = $_POST['code'];
        
        $sql = "UPDATE offers SET title=?, description=?, discount_type=?, discount_value=?, 
                start_date=?, end_date=?, min_purchase=?, code=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdssds", $title, $description, $discount_type, $discount_value, 
                         $start_date, $end_date, $min_purchase, $code, $id);
        
        if ($stmt->execute()) {
            header("Location: offers.php?success=2");
            exit;
        }
    }
    
    if (isset($_POST['delete_offer'])) {
        $id = $_POST['offer_id'];
        
        $sql = "DELETE FROM offers WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: offers.php?success=3");
            exit;
        }
    }
}

require_once('components/sidebar.php');
?>

<!-- Main Content -->
<div class="flex-1 p-8">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold">Offers & Discounts</h2>
            <a href="?action=add" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                Add New Offer
            </a>
        </div>

        <?php if ($action === 'list'): ?>
            <!-- Active Offers -->
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4">Active Offers</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    $current_date = date('Y-m-d');
                    $sql = "SELECT * FROM offers WHERE end_date >= ? ORDER BY start_date ASC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $current_date);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0):
                        while ($offer = $result->fetch_assoc()):
                    ?>
                        <div class="bg-white border rounded-lg p-6 shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="text-lg font-semibold"><?php echo htmlspecialchars($offer['title']); ?></h4>
                                <div class="flex space-x-2">
                                    <a href="?action=edit&id=<?php echo $offer['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this offer?');">
                                        <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
                                        <button type="submit" name="delete_offer" 
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($offer['description']); ?></p>
                            <div class="space-y-2">
                                <p class="text-sm">
                                    <span class="font-medium">Discount:</span>
                                    <?php 
                                    echo $offer['discount_type'] === 'percentage' 
                                        ? $offer['discount_value'] . '%' 
                                        : '$' . number_format($offer['discount_value'], 2); 
                                    ?>
                                </p>
                                <p class="text-sm">
                                    <span class="font-medium">Code:</span>
                                    <span class="bg-gray-100 px-2 py-1 rounded"><?php echo $offer['code']; ?></span>
                                </p>
                                <p class="text-sm">
                                    <span class="font-medium">Valid:</span>
                                    <?php 
                                    echo date('M d, Y', strtotime($offer['start_date'])) . ' - ' . 
                                         date('M d, Y', strtotime($offer['end_date'])); 
                                    ?>
                                </p>
                                <?php if ($offer['min_purchase'] > 0): ?>
                                    <p class="text-sm">
                                        <span class="font-medium">Min. Purchase:</span>
                                        $<?php echo number_format($offer['min_purchase'], 2); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div class="col-span-3 text-center text-gray-500 py-8">
                            No active offers found
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Expired Offers -->
            <div class="p-6 border-t border-gray-200">
                <h3 class="text-lg font-medium mb-4">Expired Offers</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $sql = "SELECT * FROM offers WHERE end_date < ? ORDER BY end_date DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $current_date);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0):
                                while ($offer = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($offer['title']); ?></td>
                                    <td class="px-6 py-4">
                                        <?php 
                                        echo $offer['discount_type'] === 'percentage' 
                                            ? $offer['discount_value'] . '%' 
                                            : '$' . number_format($offer['discount_value'], 2); 
                                        ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-gray-100 px-2 py-1 rounded"><?php echo $offer['code']; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php 
                                        echo date('M d, Y', strtotime($offer['start_date'])) . ' - ' . 
                                             date('M d, Y', strtotime($offer['end_date'])); 
                                        ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button onclick="cloneOffer(<?php echo htmlspecialchars(json_encode($offer)); ?>)"
                                                class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-copy"></i> Clone
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No expired offers found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <?php
            $offer = null;
            if ($action === 'edit' && isset($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = $conn->prepare("SELECT * FROM offers WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $offer = $stmt->get_result()->fetch_assoc();
            }
            ?>
            <!-- Add/Edit Offer Form -->
            <div class="p-6">
                <form action="" method="POST" class="space-y-6">
                    <?php if ($offer): ?>
                        <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" required
                               value="<?php echo $offer ? htmlspecialchars($offer['title']) : ''; ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" required
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"><?php echo $offer ? htmlspecialchars($offer['description']) : ''; ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Discount Type</label>
                            <select name="discount_type" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option value="percentage" <?php echo ($offer && $offer['discount_type'] === 'percentage') ? 'selected' : ''; ?>>Percentage</option>
                                <option value="fixed" <?php echo ($offer && $offer['discount_type'] === 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Discount Value</label>
                            <input type="number" name="discount_value" step="0.01" required
                                   value="<?php echo $offer ? $offer['discount_value'] : ''; ?>"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" required
                                   value="<?php echo $offer ? $offer['start_date'] : ''; ?>"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" required
                                   value="<?php echo $offer ? $offer['end_date'] : ''; ?>"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Minimum Purchase Amount</label>
                            <input type="number" name="min_purchase" step="0.01" required
                                   value="<?php echo $offer ? $offer['min_purchase'] : '0'; ?>"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Promo Code</label>
                            <input type="text" name="code" required
                                   value="<?php echo $offer ? $offer['code'] : ''; ?>"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <a href="offers.php" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">Cancel</a>
                        <button type="submit" name="<?php echo $offer ? 'edit_offer' : 'add_offer'; ?>"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            <?php echo $offer ? 'Update Offer' : 'Add Offer'; ?>
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function cloneOffer(offer) {
    // Redirect to add offer page with pre-filled data
    const params = new URLSearchParams({
        action: 'add',
        title: offer.title + ' (Copy)',
        description: offer.description,
        discount_type: offer.discount_type,
        discount_value: offer.discount_value,
        min_purchase: offer.min_purchase,
        code: offer.code + '_COPY'
    });
    
    window.location.href = 'offers.php?' + params.toString();
}
</script>

<?php
$conn->close();
?>

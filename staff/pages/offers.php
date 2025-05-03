<?php
// Handle offer operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $title = $_POST['title'];
            $description = $_POST['description'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            
            // Handle image upload
            $image = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/offers/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = 'uploads/offers/' . $fileName;
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO offers (title, description, start_date, end_date, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $description, $start_date, $end_date, $image);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Offer created successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to create offer.";
            }
            break;
            
        case 'edit':
            $id = $_POST['id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            
            // Handle image upload for edit
            $image_update = "";
            $image_param = "";
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/offers/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    // Delete old image if exists
                    $old_image_query = $conn->query("SELECT image FROM offers WHERE id = $id");
                    if ($old_image = $old_image_query->fetch_assoc()) {
                        if ($old_image['image'] && file_exists('../' . $old_image['image'])) {
                            unlink('../' . $old_image['image']);
                        }
                    }
                    
                    $image = 'uploads/offers/' . $fileName;
                    $image_update = ", image = ?";
                    $image_param = "s";
                }
            }
            
            $stmt = $conn->prepare("UPDATE offers SET title = ?, description = ?, start_date = ?, end_date = ?" . $image_update . " WHERE id = ?");
            $types = "ssss" . $image_param . "i";
            $params = [$title, $description, $start_date, $end_date];
            if ($image_param) {
                $params[] = $image;
            }
            $params[] = $id;
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Offer updated successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to update offer.";
            }
            break;
            
        case 'delete':
            $id = $_POST['id'];
            
            // Delete associated image first
            $image_query = $conn->query("SELECT image FROM offers WHERE id = $id");
            if ($image_data = $image_query->fetch_assoc()) {
                if ($image_data['image'] && file_exists('../' . $image_data['image'])) {
                    unlink('../' . $image_data['image']);
                }
            }
            
            $stmt = $conn->prepare("DELETE FROM offers WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Offer deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to delete offer.";
            }
            break;
    }
}

// Get active and upcoming offers
$current_date = date('Y-m-d');
$active_offers = $conn->query("
    SELECT * FROM offers 
    WHERE start_date <= '$current_date' AND end_date >= '$current_date'
    ORDER BY end_date ASC
");

$upcoming_offers = $conn->query("
    SELECT * FROM offers 
    WHERE start_date > '$current_date'
    ORDER BY start_date ASC
");

$expired_offers = $conn->query("
    SELECT * FROM offers 
    WHERE end_date < '$current_date'
    ORDER BY end_date DESC
    LIMIT 5
");
?>

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-primary">Special Offers & Promotions</h2>
        <button onclick="showOfferModal('add')" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
            Add New Offer
        </button>
    </div>
    
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

    <!-- Active Offers -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4">Active Offers</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if ($active_offers->num_rows === 0): ?>
                <div class="col-span-full text-center py-8 text-gray-500">
                    No active offers at the moment.
                </div>
            <?php else: ?>
                <?php while ($offer = $active_offers->fetch_assoc()): ?>
                    <div class="border rounded-lg p-4 bg-green-50">
                        <?php if ($offer['image']): ?>
                        <div class="mb-3">
                            <img src="../<?php echo htmlspecialchars($offer['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($offer['title']); ?>"
                                 class="w-full h-48 object-cover rounded-lg">
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between items-start">
                            <h4 class="font-semibold"><?php echo htmlspecialchars($offer['title']); ?></h4>
                            <div class="flex gap-2">
                                <button onclick="showOfferModal('edit', <?php echo htmlspecialchars(json_encode($offer)); ?>)"
                                        class="text-yellow-600 hover:text-yellow-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button onclick="deleteOffer(<?php echo $offer['id']; ?>)"
                                        class="text-red-600 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($offer['description']); ?></p>
                        <div class="text-sm text-gray-600 mt-2">
                            Ends: <?php echo date('M d, Y', strtotime($offer['end_date'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Offers -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4">Upcoming Offers</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if ($upcoming_offers->num_rows === 0): ?>
                <div class="col-span-full text-center py-8 text-gray-500">
                    No upcoming offers scheduled.
                </div>
            <?php else: ?>
                <?php while ($offer = $upcoming_offers->fetch_assoc()): ?>
                    <div class="border rounded-lg p-4">
                        <?php if ($offer['image']): ?>
                        <div class="mb-3">
                            <img src="../<?php echo htmlspecialchars($offer['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($offer['title']); ?>"
                                 class="w-full h-48 object-cover rounded-lg">
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between items-start">
                            <h4 class="font-semibold"><?php echo htmlspecialchars($offer['title']); ?></h4>
                            <div class="flex gap-2">
                                <button onclick="showOfferModal('edit', <?php echo htmlspecialchars(json_encode($offer)); ?>)"
                                        class="text-yellow-600 hover:text-yellow-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button onclick="deleteOffer(<?php echo $offer['id']; ?>)"
                                        class="text-red-600 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($offer['description']); ?></p>
                        <div class="text-sm text-gray-600 mt-2">
                            Starts: <?php echo date('M d, Y', strtotime($offer['start_date'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recently Expired Offers -->
    <div>
        <h3 class="text-lg font-semibold mb-4">Recently Expired Offers</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if ($expired_offers->num_rows === 0): ?>
                <div class="col-span-full text-center py-8 text-gray-500">
                    No recently expired offers.
                </div>
            <?php else: ?>
                <?php while ($offer = $expired_offers->fetch_assoc()): ?>
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <?php if ($offer['image']): ?>
                        <div class="mb-3">
                            <img src="../<?php echo htmlspecialchars($offer['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($offer['title']); ?>"
                                 class="w-full h-48 object-cover rounded-lg">
                        </div>
                        <?php endif; ?>
                        <h4 class="font-semibold"><?php echo htmlspecialchars($offer['title']); ?></h4>
                        <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($offer['description']); ?></p>
                        <div class="text-sm text-gray-600 mt-2">
                            Ended: <?php echo date('M d, Y', strtotime($offer['end_date'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Offer Modal -->
<div id="offerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4" id="modalTitle">Add New Offer</h3>
                <form id="offerForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="offerId">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Title</label>
                        <input type="text" name="title" id="offerTitle" required
                               class="border rounded w-full px-3 py-2 focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea name="description" id="offerDescription" required
                                  class="border rounded w-full px-3 py-2 focus:border-primary focus:ring-primary"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Image</label>
                        <input type="file" name="image" id="offerImage" accept="image/*"
                               class="border rounded w-full px-3 py-2 focus:border-primary focus:ring-primary">
                        <p class="text-sm text-gray-500 mt-1">Upload an image for the offer (optional)</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Start Date</label>
                        <input type="date" name="start_date" id="startDate" required
                               class="border rounded w-full px-3 py-2 focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">End Date</label>
                        <input type="date" name="end_date" id="endDate" required
                               class="border rounded w-full px-3 py-2 focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeOfferModal()"
                                class="px-4 py-2 border rounded hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/90">
                            Save Offer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showOfferModal(action, offer = null) {
    const modal = document.getElementById('offerModal');
    const form = document.getElementById('offerForm');
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const offerId = document.getElementById('offerId');
    const offerTitle = document.getElementById('offerTitle');
    const offerDescription = document.getElementById('offerDescription');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    modalTitle.textContent = action === 'add' ? 'Add New Offer' : 'Edit Offer';
    formAction.value = action;
    
    if (action === 'edit' && offer) {
        offerId.value = offer.id;
        offerTitle.value = offer.title;
        offerDescription.value = offer.description;
        startDate.value = offer.start_date;
        endDate.value = offer.end_date;
    } else {
        form.reset();
        offerId.value = '';
    }
    
    modal.classList.remove('hidden');
}

function closeOfferModal() {
    document.getElementById('offerModal').classList.add('hidden');
}

function deleteOffer(id) {
    if (confirm('Are you sure you want to delete this offer?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

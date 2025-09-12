<!-- Sub-tabs for Students and Faculty -->
<div class="mb-8">
    <div class="flex space-x-1 border-b-2 border-secondary-200">
        <button class="bg-accent-50 text-primary-600 font-semibold px-6 py-4 rounded-t-lg border-b-2 border-primary-600 hover:bg-accent-100 transition-all duration-300 text-lg" id="students-subtab" onclick="showUserSubTab('students')">
            <i class="fas fa-graduation-cap mr-2"></i>
            Students
        </button>
        <button class="text-secondary-600 font-semibold px-6 py-4 rounded-t-lg hover:bg-accent-50 hover:text-primary-600 transition-all duration-300 text-lg" id="faculty-subtab" onclick="showUserSubTab('faculty')">
            <i class="fas fa-chalkboard-teacher mr-2"></i>
            Faculty
        </button>
    </div>
</div>

<!-- Students Sub-tab Content -->
<div id="students-content" class="user-subtab-content">
    <!-- Top Section - Add Student Actions -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h4 class="text-2xl font-semibold text-secondary-900 mb-2">
                    <i class="fas fa-user-plus mr-3 text-primary-600"></i>
                    Add New Students
                </h4>
                <p class="text-lg text-secondary-600">Add students to the system</p>
            </div>
            <div class="flex space-x-3">
                <button class="bg-primary-600 hover:bg-primary-700 text-accent-50 px-8 py-4 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1 text-lg" onclick="showAddStudentModal()">
                    <i class="fas fa-plus mr-2"></i>
                    Add Student
                </button>
            </div>
        </div>
    </div>

    <!-- Students Section - Organized by Year & Section -->
    <div class="mb-8">
        <h5 class="text-xl font-semibold text-secondary-900 mb-4">
            <i class="fas fa-graduation-cap mr-3 text-primary-600"></i>
            Students by Year & Section
        </h5>
    
        <!-- Year-Section Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <?php 
            $firstSection = true;
            foreach ($yearSections as $yearSection => $count): 
            ?>
                <button class="year-section-tab <?= $firstSection ? 'active' : '' ?> bg-accent-100 border border-secondary-300 text-secondary-600 px-6 py-3 rounded-lg text-base font-semibold transition-all duration-300 hover:bg-primary-600 hover:text-accent-50 hover:border-primary-600" 
                        data-section="section-<?= str_replace(' ', '-', strtolower($yearSection)) ?>">
                    <?= $yearSection ?>
                    <span class="bg-green-500 text-accent-50 rounded-full w-6 h-6 inline-flex items-center justify-center text-sm font-bold ml-2"><?= $count ?></span>
                </button>
            <?php 
                $firstSection = false;
            endforeach; 
            ?>
        </div>

        <!-- Student Sections Content -->
        <?php 
        $firstSection = true;
        foreach ($yearSections as $yearSection => $count): 
            $sectionId = 'section-' . str_replace(' ', '-', strtolower($yearSection));
            $sectionStudents = array_filter($students, function($student) use ($yearSection) {
                return ($student['year_level'] . ' ' . $student['section']) === $yearSection;
            });
        ?>
            <div class="student-section <?= $firstSection ? 'active' : '' ?> <?= !$firstSection ? 'hidden' : '' ?>" 
                 id="<?= $sectionId ?>">
                
                <!-- Section Header -->
                <div class="bg-accent-100 p-6 rounded-lg mb-4 border-l-4 border-primary-600">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-xl font-bold text-primary-600">
                                <i class="fas fa-users mr-3"></i>
                                <?= $yearSection ?>
                            </h6>
                        </div>
                        <div>
                            <span class="text-secondary-600 text-lg"><?= $count ?> students</span>
                        </div>
                    </div>
                </div>

                <!-- Student Cards -->
                <?php foreach ($sectionStudents as $student): ?>
                    <div class="bg-accent-50 border border-secondary-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="flex justify-between items-center">
                            <div class="flex-1">
                                <div class="text-xl font-bold text-primary-600 mb-2">
                                    <i class="fas fa-user-graduate mr-3"></i>
                                    <?= htmlspecialchars($student['full_name']) ?>
                                </div>
                                <div class="text-secondary-600 text-base mb-2">
                                    <i class="fas fa-id-card mr-3"></i>
                                    <?= htmlspecialchars($student['school_id']) ?>
                                </div>
                                <div class="text-secondary-500 text-sm">
                                    <i class="fas fa-calendar mr-3"></i>
                                    Added on <?= date('M d, Y', strtotime($student['created_at'])) ?>
                                </div>
                            </div>
                            <div class="flex space-x-3">
                                <button class="bg-yellow-500 hover:bg-yellow-600 text-accent-50 px-6 py-3 rounded text-base transition-all duration-300" onclick="editStudent(<?= $student['user_id'] ?>)">
                                    <i class="fas fa-edit mr-2"></i>
                                    Edit
                                </button>
                                <button class="bg-red-500 hover:bg-red-600 text-accent-50 px-6 py-3 rounded text-base transition-all duration-300" onclick="deleteStudent(<?= $student['user_id'] ?>)">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php 
            $firstSection = false;
        endforeach; 
        ?>
    </div>
</div>

<!-- Faculty Sub-tab Content -->
<div id="faculty-content" class="user-subtab-content hidden">
    <!-- Top Section - Add Faculty Actions -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h4 class="text-2xl font-semibold text-secondary-900 mb-2">
                    <i class="fas fa-user-plus mr-3 text-primary-600"></i>
                    Add New Faculty
                </h4>
                <p class="text-lg text-secondary-600">Add faculty members to the system</p>
            </div>
            <div class="flex space-x-3">
                <button class="bg-primary-600 hover:bg-primary-700 text-accent-50 px-8 py-4 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1 text-lg" onclick="showAddFacultyModal()">
                    <i class="fas fa-plus mr-2"></i>
                    Add Faculty
                </button>
            </div>
        </div>
    </div>

    <!-- Faculty Section -->
    <div class="mb-8">
        <h5 class="text-xl font-semibold text-secondary-900 mb-4">
            <i class="fas fa-chalkboard-teacher mr-3 text-primary-600"></i>
            Faculty Members
        </h5>
        
        <div class="bg-accent-100 p-6 rounded-lg mb-4 border-l-4 border-primary-600">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-xl font-bold text-primary-600">
                        <i class="fas fa-users mr-3"></i>
                        All Faculty
                    </h6>
                </div>
                <div>
                    <span class="text-secondary-600 text-lg"><?= count($faculty) ?> faculty members</span>
                </div>
            </div>
        </div>

        <!-- Faculty Cards -->
        <?php foreach ($faculty as $facultyMember): ?>
            <div class="bg-accent-50 border border-secondary-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <div class="text-xl font-bold text-primary-600 mb-2">
                            <i class="fas fa-user-tie mr-3"></i>
                            <?= htmlspecialchars($facultyMember['full_name']) ?>
                        </div>
                        <div class="text-secondary-600 text-base mb-2">
                            <i class="fas fa-id-card mr-3"></i>
                            <?= htmlspecialchars($facultyMember['school_id']) ?>
                        </div>
                        <div class="text-secondary-500 text-sm">
                            <i class="fas fa-calendar mr-3"></i>
                            Added on <?= date('M d, Y', strtotime($facultyMember['created_at'])) ?>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button class="bg-yellow-500 hover:bg-yellow-600 text-accent-50 px-6 py-3 rounded text-base transition-all duration-300" onclick="editFaculty(<?= $facultyMember['user_id'] ?>)">
                            <i class="fas fa-edit mr-2"></i>
                            Edit
                        </button>
                        <button class="bg-red-500 hover:bg-red-600 text-accent-50 px-6 py-3 rounded text-base transition-all duration-300" onclick="deleteFaculty(<?= $facultyMember['user_id'] ?>)">
                            <i class="fas fa-trash mr-2"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// User Sub-tab Switching
function showUserSubTab(tabName) {
    // Hide all sub-tab contents
    document.querySelectorAll('.user-subtab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all sub-tabs
    document.querySelectorAll('[id$="-subtab"]').forEach(tab => {
        tab.classList.remove('bg-accent-50', 'text-primary-600', 'border-primary-600');
        tab.classList.add('text-secondary-600');
    });
    
    // Show selected sub-tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active class to selected sub-tab
    const activeTab = document.getElementById(tabName + '-subtab');
    activeTab.classList.remove('text-secondary-600');
    activeTab.classList.add('bg-accent-50', 'text-primary-600', 'border-primary-600');
}

// Edit Student Function
function editStudent(studentId) {
    // Fetch student data and populate form
    fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/get-student-data?user_id=${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.data);
                showEditStudentModal(studentId);
            } else {
                alert('Error loading student data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading student data');
        });
}

// Populate Edit Form with Student Data
function populateEditForm(studentData) {
    document.getElementById('edit_school_id').value = studentData.school_id || '';
    document.getElementById('edit_full_name').value = studentData.full_name || '';
    document.getElementById('edit_year_level').value = studentData.year_level || '';
    document.getElementById('edit_section').value = studentData.section || '';
}

// Show Edit Student Modal
function showEditStudentModal(studentId) {
    document.getElementById('editStudentModal').classList.remove('hidden');
    document.getElementById('editStudentId').value = studentId;
}

// Delete Student Function
function deleteStudent(studentId) {
    showDeleteStudentConfirmation(studentId);
}

// Edit Faculty Function
function editFaculty(facultyId) {
    // TODO: Fetch faculty data and populate form
    console.log('Edit faculty:', facultyId);
    // For now, show a simple form
    showEditFacultyModal(facultyId);
}

// Show Edit Faculty Modal
function showEditFacultyModal(facultyId) {
    document.getElementById('editFacultyModal').classList.remove('hidden');
    document.getElementById('editFacultyId').value = facultyId;
}

// Delete Faculty Function
function deleteFaculty(facultyId) {
    if (confirm('Are you sure you want to delete this faculty member?')) {
        // Create and submit delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/delete-faculty';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = facultyId;
        
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Add Student Modal
function showAddStudentModal() {
    document.getElementById('addStudentModal').classList.remove('hidden');
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Reset Form Fields
function resetForm(formId) {
    document.getElementById(formId).reset();
}

// Add Faculty Modal
function showAddFacultyModal() {
    document.getElementById('addFacultyModal').classList.remove('hidden');
}

// Show Delete Student Confirmation
function showDeleteStudentConfirmation(studentId) {
    document.getElementById('deleteStudentModal').classList.remove('hidden');
    document.getElementById('deleteStudentId').value = studentId;
}

// Confirm Delete Student
function confirmDeleteStudent() {
    const studentId = document.getElementById('deleteStudentId').value;
    // Create and submit delete form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/delete-student';
    
    const userIdInput = document.createElement('input');
    userIdInput.type = 'hidden';
    userIdInput.name = 'user_id';
    userIdInput.value = studentId;
    
    form.appendChild(userIdInput);
    document.body.appendChild(form);
    form.submit();
}

// Close Delete Student Modal
function closeDeleteStudentModal() {
    document.getElementById('deleteStudentModal').classList.add('hidden');
}

// Year-Section Tab Switching
document.addEventListener('DOMContentLoaded', function() {
    const yearSectionTabs = document.querySelectorAll('.year-section-tab');
    const studentSections = document.querySelectorAll('.student-section');

    yearSectionTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetSection = this.getAttribute('data-section');
            
            // Update active tab
            yearSectionTabs.forEach(t => {
                t.classList.remove('active', 'bg-primary-600', 'text-accent-50', 'border-primary-600');
                t.classList.add('bg-accent-100', 'text-secondary-600', 'border-secondary-300');
            });
            this.classList.add('active', 'bg-primary-600', 'text-accent-50', 'border-primary-600');
            this.classList.remove('bg-accent-100', 'text-secondary-600', 'border-secondary-300');
            
            // Show/hide sections
            studentSections.forEach(section => {
                if (section.id === targetSection) {
                    section.classList.remove('hidden');
                } else {
                    section.classList.add('hidden');
                }
            });
        });
    });

    // Show first section by default
    if (yearSectionTabs.length > 0) {
        yearSectionTabs[0].click();
    }
});
</script>

<!-- Delete Student Confirmation Modal -->
<div id="deleteStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-accent-50 rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="p-8 text-center">
            <div class="mb-6">
                <i class="fas fa-exclamation-triangle text-6xl text-red-500 mb-4"></i>
                <h3 class="text-2xl font-bold text-secondary-900 mb-2">Delete Student</h3>
                <p class="text-lg text-secondary-600">Are you sure you want to delete this student? This action cannot be undone.</p>
            </div>
            <div class="flex justify-center space-x-4">
                <button onclick="confirmDeleteStudent()" class="bg-red-500 hover:bg-red-600 text-accent-50 px-8 py-3 rounded-lg font-semibold transition-all duration-300 text-lg">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
                <button onclick="closeDeleteStudentModal()" class="bg-secondary-600 hover:bg-secondary-700 text-accent-50 px-8 py-3 rounded-lg font-semibold transition-all duration-300 text-lg">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>
            </div>
            <input type="hidden" id="deleteStudentId" value="">
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-accent-50 rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="p-6 border-b border-secondary-200">
            <h3 class="text-2xl font-semibold text-secondary-900">
                <i class="fas fa-edit mr-3 text-primary-600"></i>
                Edit Student
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="editStudentForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/edit-student" method="POST">
                <input type="hidden" id="editStudentId" name="user_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="edit_school_id" class="block text-base font-medium text-secondary-700 mb-3">School ID *</label>
                        <input type="text" id="edit_school_id" name="school_id" required 
                               class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                    </div>
                    <div>
                        <label for="edit_full_name" class="block text-base font-medium text-secondary-700 mb-3">Full Name *</label>
                        <input type="text" id="edit_full_name" name="full_name" required 
                               class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="edit_year_level" class="block text-base font-medium text-secondary-700 mb-3">Year Level *</label>
                        <select id="edit_year_level" name="year_level" required 
                                class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                            <option value="">Select Year Level</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_section" class="block text-base font-medium text-secondary-700 mb-3">Section *</label>
                        <select id="edit_section" name="section" required 
                                class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                            <option value="">Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="role" value="student">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-4 p-6 border-t border-secondary-200">
            <button onclick="closeModal('editStudentModal')" 
                    class="px-6 py-3 text-secondary-600 bg-accent-100 hover:bg-accent-200 rounded-lg transition-colors text-lg font-medium">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitEditForm()" 
                    class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-accent-50 rounded-lg font-semibold transition-colors text-lg">
                <i class="fas fa-save mr-2"></i>
                Update Student
            </button>
        </div>
    </div>
</div>

<script>
// Submit edit form function
function submitEditForm() {
    const form = document.getElementById('editStudentForm');
    if (form.checkValidity()) {
        // Submit form to controller
        form.submit();
        // Close modal after submission
        setTimeout(() => {
            closeModal('editStudentModal');
        }, 100);
    } else {
        // Show validation errors
        form.reportValidity();
    }
}

// Close edit modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editStudentModal');
    editModal.addEventListener('click', function(e) {
        if (e.target === editModal) {
            closeModal('editStudentModal');
        }
    });
});
</script>

<!-- Add Student Modal -->
<div id="addStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-accent-50 rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="p-6 border-b border-secondary-200">
            <h3 class="text-2xl font-semibold text-secondary-900">
                <i class="fas fa-user-plus mr-3 text-primary-600"></i>
                Add New Student
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="addStudentForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/add-student" method="POST" onsubmit="handleFormSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="school_id" class="block text-base font-medium text-secondary-700 mb-3">School ID *</label>
                        <input type="text" id="school_id" name="school_id" required 
                               class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                    </div>
                    <div>
                        <label for="full_name" class="block text-base font-medium text-secondary-700 mb-3">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required 
                               class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="year_level" class="block text-base font-medium text-secondary-700 mb-3">Year Level *</label>
                        <select id="year_level" name="year_level" required 
                                class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                            <option value="">Select Year Level</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label for="section" class="block text-base font-medium text-secondary-700 mb-3">Section *</label>
                        <select id="section" name="section" required 
                                class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                            <option value="">Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-base font-medium text-secondary-700 mb-3">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Leave blank for default password"
                           class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base">
                    <p class="text-sm text-secondary-500 mt-2">Default password will be: School ID + Full Name</p>
                </div>

                <input type="hidden" name="role" value="student">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-4 p-6 border-t border-secondary-200">
            <button onclick="closeModal('addStudentModal')" 
                    class="px-6 py-3 text-accent-50 bg-red-500 hover:bg-red-600 rounded-lg transition-colors text-lg font-medium">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitForm()" 
                    class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-accent-50 rounded-lg font-semibold transition-colors text-lg">
                <i class="fas fa-save mr-2"></i>
                Add Student
            </button>
        </div>
    </div>
</div>

<script>
// Handle form submission
function handleFormSubmit(event) {
    // Form will submit normally to controller
    // Controller will handle validation and redirect
}

// Submit form function
function submitForm() {
    const form = document.getElementById('addStudentForm');
    if (form.checkValidity()) {
        // Submit form to controller
        form.submit();
        // Reset form fields after submission
        setTimeout(() => {
            resetForm('addStudentForm');
            closeModal('addStudentModal');
        }, 100);
    } else {
        // Show validation errors
        form.reportValidity();
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addStudentModal');
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal('addStudentModal');
        }
    });
});
</script>

<!-- Edit Faculty Modal -->
<div id="editFacultyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-edit mr-2 text-primary-600"></i>
                Edit Faculty
            </h3>
            <button onclick="closeModal('editFacultyModal')" class="text-grey-400 hover:text-grey-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="editFacultyForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/edit-faculty" method="POST">
                <input type="hidden" id="editFacultyId" name="user_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_faculty_school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="edit_faculty_school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="edit_faculty_full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="edit_faculty_full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>

                <input type="hidden" name="role" value="faculty">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('editFacultyModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitEditFacultyForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Update Faculty
            </button>
        </div>
    </div>
</div>

<script>
// Submit edit faculty form function
function submitEditFacultyForm() {
    const form = document.getElementById('editFacultyForm');
    if (form.checkValidity()) {
        // Submit form to controller
        form.submit();
        // Close modal after submission
        setTimeout(() => {
            closeModal('editFacultyModal');
        }, 100);
    } else {
        // Show validation errors
        form.reportValidity();
    }
}

// Close edit faculty modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const editFacultyModal = document.getElementById('editFacultyModal');
    editFacultyModal.addEventListener('click', function(e) {
        if (e.target === editFacultyModal) {
            closeModal('editFacultyModal');
        }
    });
});
</script>

<!-- Add Faculty Modal -->
<div id="addFacultyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-user-plus mr-2 text-primary-600"></i>
                Add New Faculty
            </h3>
            <button onclick="closeModal('addFacultyModal')" class="text-grey-400 hover:text-grey-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="addFacultyForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/add-faculty" method="POST" onsubmit="handleFacultyFormSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="faculty_school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="faculty_school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="faculty_full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="faculty_full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="faculty_password" class="block text-sm font-medium text-grey-700 mb-2">Password</label>
                    <input type="password" id="faculty_password" name="password" 
                           placeholder="Leave blank for default password"
                           class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-grey-500 mt-1">Default password will be: School ID + Full Name</p>
                </div>

                <input type="hidden" name="role" value="faculty">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('addFacultyModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitFacultyForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Add Faculty
            </button>
        </div>
    </div>
</div>

<script>
// Handle faculty form submission
function handleFacultyFormSubmit(event) {
    // Form will submit normally to controller
    // Controller will handle validation and redirect
}

// Submit faculty form function
function submitFacultyForm() {
    const form = document.getElementById('addFacultyForm');
    if (form.checkValidity()) {
        // Submit form to controller
        form.submit();
        // Reset form fields after submission
        setTimeout(() => {
            resetForm('addFacultyForm');
            closeModal('addFacultyModal');
        }, 100);
    } else {
        // Show validation errors
        form.reportValidity();
    }
}

// Close faculty modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addFacultyModal');
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal('addFacultyModal');
        }
    });
});
</script>
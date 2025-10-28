<!-- User Management Sub-tabs -->
<div class="mb-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h4 class="text-2xl font-bold text-primary-600 mb-2">
                <i class="fas fa-users mr-3"></i>
                User Management
            </h4>
            <p class="text-lg text-secondary-600">Manage students and faculty members</p>
        </div>
    </div>
    
    <!-- Sub-tab Navigation -->
    <div class="border-b-2 border-secondary-200 mb-6">
        <div class="flex space-x-1">
            <button class="bg-accent-500 text-primary-600 font-semibold px-6 py-4 rounded-t-lg border-b-2 border-primary-600 hover:bg-accent-600 transition-all duration-300 text-lg" id="students-subtab" onclick="showUserSubTab('students')">
                <i class="fas fa-graduation-cap mr-2"></i>
                Students
            </button>
            <button class="text-secondary-600 font-semibold px-6 py-4 rounded-t-lg hover:bg-accent-500 hover:text-primary-600 transition-all duration-300 text-lg" id="faculty-subtab" onclick="showUserSubTab('faculty')">
                <i class="fas fa-chalkboard-teacher mr-2"></i>
                Faculty
            </button>
        </div>
    </div>
</div>

<!-- Students Sub-tab Content -->
<div id="students-content" class="user-subtab-content">
    <!-- Add Student Actions -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h5 class="text-xl font-semibold text-primary-600 mb-2">
                    <i class="fas fa-user-plus mr-2"></i>
                    Add New Student
                </h5>
                <p class="text-secondary-600">Add a new student to the system</p>
            </div>
            <div>
                <button class="bg-primary-600 hover:bg-primary-700 text-accent-500 px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1 text-lg" onclick="showAddStudentModal()">
                    <i class="fas fa-plus mr-2"></i>
                    Add Student
                </button>
            </div>
        </div>
    </div>

    <!-- Students Section - Organized by Year & Section -->
    <div class="mb-8">
    <h5 class="text-lg font-semibold text-grey-800 mb-4">
        <i class="fas fa-graduation-cap mr-2 text-primary-600"></i>
        Students by Year & Section
    </h5>
    
    <!-- Year-Section Tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
        <?php 
        $firstSection = true;
        foreach ($yearSections as $yearSection => $count): 
        ?>
            <button class="year-section-tab <?= $firstSection ? 'active' : '' ?> bg-grey-100 border border-grey-300 text-grey-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300 hover:bg-primary-600 hover:text-white hover:border-primary-600" 
                    data-section="section-<?= str_replace(' ', '-', strtolower($yearSection)) ?>">
                <?= $yearSection ?>
                <span class="bg-green-500 text-white rounded-full w-5 h-5 inline-flex items-center justify-center text-xs font-bold ml-2"><?= $count ?></span>
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
            <div class="bg-grey-100 p-4 rounded-lg mb-4 border-l-4 border-primary-600">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-lg font-bold text-primary-600">
                            <i class="fas fa-users mr-2"></i>
                            <?= $yearSection ?>
                        </h6>
                    </div>
                    <div>
                        <span class="text-grey-600 text-sm"><?= $count ?> students</span>
                    </div>
                </div>
            </div>

            <!-- Student Cards -->
            <?php foreach ($sectionStudents as $student): ?>
                <div class="bg-accent-500 border border-secondary-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1" 
                     data-student-id="<?= $student['user_id'] ?>"
                     data-school-id="<?= htmlspecialchars($student['school_id']) ?>"
                     data-full-name="<?= htmlspecialchars($student['full_name']) ?>"
                     data-year-level="<?= htmlspecialchars($student['year_level']) ?>"
                     data-section="<?= htmlspecialchars($student['section']) ?>">
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <div class="text-lg font-bold text-primary-600 mb-2">
                                <i class="fas fa-user-graduate mr-2"></i>
                                <?= htmlspecialchars($student['full_name']) ?>
                            </div>
                            <div class="text-grey-600 text-sm mb-2">
                                <i class="fas fa-id-card mr-2"></i>
                                <?= htmlspecialchars($student['school_id']) ?>
                            </div>
                            <div class="text-grey-500 text-xs">
                                <i class="fas fa-calendar mr-2"></i>
                                Added on <?= date('M d, Y', strtotime($student['created_at'])) ?>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="editStudent(<?= $student['user_id'] ?>)">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </button>
                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="deleteStudent(<?= $student['user_id'] ?>)">
                                <i class="fas fa-trash mr-1"></i>
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
    <!-- Add Faculty Actions -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h5 class="text-xl font-semibold text-primary-600 mb-2">
                    <i class="fas fa-user-plus mr-2"></i>
                    Add New Faculty
                </h5>
                <p class="text-secondary-600">Add a new faculty member to the system</p>
            </div>
            <div>
                <button class="bg-primary-600 hover:bg-primary-700 text-accent-500 px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1 text-lg" onclick="showAddFacultyModal()">
                    <i class="fas fa-plus mr-2"></i>
                    Add Faculty
                </button>
            </div>
        </div>
    </div>

    <!-- Faculty Section -->
    <div class="mt-8">
    <h5 class="text-lg font-semibold text-grey-800 mb-4">
        <i class="fas fa-chalkboard-teacher mr-2 text-primary-600"></i>
        Faculty Members
    </h5>
    
    <div class="bg-grey-100 p-4 rounded-lg mb-4 border-l-4 border-primary-600">
        <div class="flex justify-between items-center">
            <div>
                <h6 class="text-lg font-bold text-primary-600">
                    <i class="fas fa-users mr-2"></i>
                    All Faculty
                </h6>
            </div>
            <div>
                <span class="text-grey-600 text-sm"><?= count($faculty) ?> faculty members</span>
            </div>
        </div>
    </div>

    <!-- Faculty Cards -->
    <?php foreach ($faculty as $facultyMember): ?>
        <div class="bg-white border border-grey-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            <div class="flex justify-between items-center">
                <div class="flex-1">
                    <div class="text-lg font-bold text-primary-600 mb-2">
                        <i class="fas fa-user-tie mr-2"></i>
                        <?= htmlspecialchars($facultyMember['full_name']) ?>
                    </div>
                    <div class="text-grey-600 text-sm mb-2">
                        <i class="fas fa-id-card mr-2"></i>
                        <?= htmlspecialchars($facultyMember['school_id']) ?>
                    </div>
                    <div class="text-grey-500 text-xs">
                        <i class="fas fa-calendar mr-2"></i>
                        Added on <?= date('M d, Y', strtotime($facultyMember['created_at'])) ?>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="editFaculty(<?= $facultyMember['user_id'] ?>)">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                    </button>
                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="deleteFaculty(<?= $facultyMember['user_id'] ?>)">
                        <i class="fas fa-trash mr-1"></i>
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
        tab.classList.remove('bg-accent-500', 'text-primary-600', 'border-primary-600');
        tab.classList.add('text-secondary-600');
    });
    
    // Show selected sub-tab content
    const tabContent = document.getElementById(tabName + '-content');
    if (tabContent) {
        tabContent.classList.remove('hidden');
    }
    
    // Add active class to selected sub-tab
    const activeTab = document.getElementById(tabName + '-subtab');
    if (activeTab) {
        activeTab.classList.remove('text-secondary-600');
        activeTab.classList.add('bg-accent-500', 'text-primary-600', 'border-primary-600');
    }
}

// Edit Student Function
function editStudent(studentId) {
    console.log('Edit student:', studentId);
    
    // Find the student data from the current page data
    const studentData = findStudentData(studentId);
    if (studentData) {
        populateEditForm(studentData);
        showEditStudentModal(studentId);
    } else {
        // Fallback: show modal without pre-populated data
        showEditStudentModal(studentId);
    }
}

// Find student data from the page
function findStudentData(studentId) {
    // Find the student card with the matching ID
    const studentCard = document.querySelector(`[data-student-id="${studentId}"]`);
    if (studentCard) {
        return {
            school_id: studentCard.getAttribute('data-school-id'),
            full_name: studentCard.getAttribute('data-full-name'),
            year_level: studentCard.getAttribute('data-year-level'),
            section: studentCard.getAttribute('data-section')
        };
    }
    console.log('Student data not found for ID:', studentId);
    return null;
}

// Populate edit form with student data
function populateEditForm(studentData) {
    if (studentData) {
        document.getElementById('edit_school_id').value = studentData.school_id || '';
        document.getElementById('edit_full_name').value = studentData.full_name || '';
        document.getElementById('edit_year_level').value = studentData.year_level || '';
        document.getElementById('edit_section').value = studentData.section || '';
    }
}

// Show Edit Student Modal
function showEditStudentModal(studentId) {
    document.getElementById('editStudentModal').classList.remove('hidden');
    document.getElementById('editStudentId').value = studentId;
}

// Delete Student Function
function deleteStudent(studentId) {
    // Get student name for confirmation
    const studentCard = document.querySelector(`[data-student-id="${studentId}"]`);
    const studentName = studentCard ? studentCard.getAttribute('data-full-name') : 'this student';
    
    // Set the student ID for deletion
    document.getElementById('deleteStudentId').value = studentId;
    document.getElementById('deleteStudentName').textContent = studentName;
    
    // Show the delete confirmation modal
    document.getElementById('deleteStudentModal').classList.remove('hidden');
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

// Confirm Delete Student Function
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

// Cancel Delete Student Function
function cancelDeleteStudent() {
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
                t.classList.remove('active', 'bg-primary-600', 'text-white', 'border-primary-600');
                t.classList.add('bg-grey-100', 'text-grey-600', 'border-grey-300');
            });
            this.classList.add('active', 'bg-primary-600', 'text-white', 'border-primary-600');
            this.classList.remove('bg-grey-100', 'text-grey-600', 'border-grey-300');
            
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

<!-- Edit Student Modal -->
<div id="editStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-edit mr-2 text-primary-600"></i>
                Edit Student
            </h3>
            <button onclick="closeModal('editStudentModal')" class="text-grey-400 hover:text-grey-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="editStudentForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/edit-student" method="POST">
                <input type="hidden" id="editStudentId" name="user_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="edit_school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="edit_full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="edit_full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_year_level" class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select id="edit_year_level" name="year_level" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Year Level</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_section" class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select id="edit_section" name="section" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
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
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('editStudentModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitEditForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
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
<div id="addStudentModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-accent-500 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 transform transition-all duration-300">
        <!-- Modal Header -->
        <div class="p-6 border-b border-secondary-200">
            <h3 class="text-2xl font-bold text-primary-600 text-center">
                <i class="fas fa-user-plus mr-3"></i>
                Add New Student
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="addStudentForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/add-student" method="POST" onsubmit="handleFormSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="year_level" class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select id="year_level" name="year_level" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Year Level</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label for="section" class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select id="section" name="section" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-grey-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Leave blank for default password"
                           class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-grey-500 mt-1">Default password will be: School ID + Full Name</p>
                </div>

                <input type="hidden" name="role" value="student">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-center space-x-4 p-6 border-t border-secondary-200">
            <button onclick="closeModal('addStudentModal')" 
                    class="px-8 py-3 bg-red-500 hover:bg-red-600 text-accent-500 font-semibold rounded-lg transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg text-lg">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitForm()" 
                    class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-accent-500 font-semibold rounded-lg transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg text-lg">
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

<!-- Delete Student Confirmation Modal -->
<div id="deleteStudentModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-accent-500 rounded-2xl shadow-2xl p-8 w-full max-w-md mx-4 transform transition-all duration-300">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-primary-600 mb-4">Confirm Deletion</h3>
            <p class="text-lg text-secondary-600 mb-2">Are you sure you want to delete</p>
            <p class="text-xl font-semibold text-primary-600 mb-8" id="deleteStudentName">this student</p>
            
            <div class="flex space-x-4">
                <button onclick="cancelDeleteStudent()" 
                        class="flex-1 bg-secondary-500 hover:bg-secondary-600 text-accent-500 font-semibold py-3 px-6 rounded-lg transition-all duration-300">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>
                <button onclick="confirmDeleteStudent()" 
                        class="flex-1 bg-red-500 hover:bg-red-600 text-accent-500 font-semibold py-3 px-6 rounded-lg transition-all duration-300">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Close delete modal when clicking outside
document.getElementById('deleteStudentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        cancelDeleteStudent();
    }
});

// Close delete modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('deleteStudentModal').classList.contains('hidden')) {
        cancelDeleteStudent();
    }
});
</script>
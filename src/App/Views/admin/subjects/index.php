<!-- Top Section - Add Subject Actions -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h4 class="text-xl font-semibold text-grey-800 mb-1">
                <i class="fas fa-book mr-2 text-primary-600"></i>
                Manage Subjects
            </h4>
            <p class="text-grey-600">Add, edit, and manage academic subjects</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1" onclick="showAddSubjectModal()">
                <i class="fas fa-plus mr-2"></i>
                Add Subject
            </button>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-grey-200">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="searchInput" class="block text-sm font-medium text-grey-700 mb-2">Search Subjects</label>
            <input type="text" id="searchInput" placeholder="Search by code, name, or description..." 
                   class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
        </div>
        <div>
            <label for="yearLevelFilter" class="block text-sm font-medium text-grey-700 mb-2">Year Level</label>
            <select id="yearLevelFilter" class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <option value="">All Year Levels</option>
                <?php foreach ($yearLevels as $key => $value): ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="semesterFilter" class="block text-sm font-medium text-grey-700 mb-2">Semester</label>
            <select id="semesterFilter" class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <option value="">All Semesters</option>
                <?php foreach ($semesters as $key => $value): ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end">
            <button onclick="clearFilters()" class="w-full bg-grey-500 hover:bg-grey-600 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-300">
                <i class="fas fa-times mr-2"></i>
                Clear Filters
            </button>
        </div>
    </div>
</div>

<!-- Subjects List -->
<div id="subjectsList">
    <?php if (empty($subjects)): ?>
        <div class="text-center py-12">
            <i class="fas fa-book text-6xl text-grey-400 mb-4"></i>
            <h4 class="text-xl font-semibold text-grey-700 mb-2">No Subjects Found</h4>
            <p class="text-grey-500">Start by adding your first subject using the button above.</p>
        </div>
    <?php else: ?>
        <!-- Year Level Groups -->
        <?php 
        $groupedSubjects = [];
        foreach ($subjects as $subject) {
            $key = $subject['year_level'] . ' - ' . $subject['semester'];
            if (!isset($groupedSubjects[$key])) {
                $groupedSubjects[$key] = [];
            }
            $groupedSubjects[$key][] = $subject;
        }
        ?>

        <?php foreach ($groupedSubjects as $groupKey => $groupSubjects): ?>
            <div class="mb-8">
                <!-- Group Header -->
                <div class="bg-grey-100 p-4 rounded-lg mb-4 border-l-4 border-primary-600">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-bold text-primary-600">
                                <i class="fas fa-graduation-cap mr-2"></i>
                                <?= htmlspecialchars($groupKey) ?>
                            </h6>
                        </div>
                        <div>
                            <span class="text-grey-600 text-sm"><?= count($groupSubjects) ?> subject<?= count($groupSubjects) > 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Subject Cards -->
                <?php foreach ($groupSubjects as $subject): ?>
                    <div class="bg-white border border-grey-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <div class="text-lg font-bold text-primary-600">
                                        <i class="fas fa-book mr-2"></i>
                                        <?= htmlspecialchars($subject['subject_name']) ?>
                                    </div>
                                    <span class="ml-3 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                                        <?= htmlspecialchars($subject['subject_code']) ?>
                                    </span>
                                    <span class="ml-2 bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">
                                        <?= htmlspecialchars($subject['units']) ?> unit<?= $subject['units'] > 1 ? 's' : '' ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($subject['description'])): ?>
                                    <div class="text-grey-600 text-sm mb-2">
                                        <?= htmlspecialchars($subject['description']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-grey-500 text-xs">
                                    <i class="fas fa-calendar mr-2"></i>
                                    Added on <?= date('M d, Y', strtotime($subject['created_at'])) ?>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="editSubject(<?= $subject['subject_id'] ?>)">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </button>
                                <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="deleteSubject(<?= $subject['subject_id'] ?>)">
                                    <i class="fas fa-trash mr-1"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// Search and Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const yearLevelFilter = document.getElementById('yearLevelFilter');
    const semesterFilter = document.getElementById('semesterFilter');

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterSubjects();
    });

    // Filter functionality
    yearLevelFilter.addEventListener('change', function() {
        filterSubjects();
    });

    semesterFilter.addEventListener('change', function() {
        filterSubjects();
    });
});

function filterSubjects() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const yearLevel = document.getElementById('yearLevelFilter').value;
    const semester = document.getElementById('semesterFilter').value;
    
    const subjectCards = document.querySelectorAll('#subjectsList .bg-white.border');
    
    subjectCards.forEach(card => {
        const subjectName = card.querySelector('.text-primary-600').textContent.toLowerCase();
        const subjectCode = card.querySelector('.bg-blue-100').textContent.toLowerCase();
        const subjectDescription = card.querySelector('.text-grey-600')?.textContent.toLowerCase() || '';
        const yearLevelText = card.closest('.mb-8').querySelector('.text-primary-600').textContent;
        
        const matchesSearch = subjectName.includes(searchTerm) || 
                            subjectCode.includes(searchTerm) || 
                            subjectDescription.includes(searchTerm);
        
        const matchesYearLevel = !yearLevel || yearLevelText.includes(yearLevel);
        const matchesSemester = !semester || yearLevelText.includes(semester);
        
        if (matchesSearch && matchesYearLevel && matchesSemester) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('yearLevelFilter').value = '';
    document.getElementById('semesterFilter').value = '';
    filterSubjects();
}

// Edit Subject Function
function editSubject(subjectId) {
    // Fetch subject data and populate form
    fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/subjects/${subjectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                populateEditForm(data.data);
                showEditSubjectModal();
            } else {
                alert('Error loading subject data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading subject data');
        });
}

function populateEditForm(subject) {
    document.getElementById('editSubjectId').value = subject.subject_id;
    document.getElementById('edit_subject_code').value = subject.subject_code;
    document.getElementById('edit_subject_name').value = subject.subject_name;
    document.getElementById('edit_description').value = subject.description;
    document.getElementById('edit_units').value = subject.units;
    document.getElementById('edit_year_level').value = subject.year_level;
    document.getElementById('edit_semester').value = subject.semester;
}

// Delete Subject Function
function deleteSubject(subjectId) {
    if (confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/subjects/delete';
        
        const subjectIdInput = document.createElement('input');
        subjectIdInput.type = 'hidden';
        subjectIdInput.name = 'subject_id';
        subjectIdInput.value = subjectId;
        
        form.appendChild(subjectIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Add Subject Modal
function showAddSubjectModal() {
    document.getElementById('addSubjectModal').classList.remove('hidden');
}

// Show Edit Subject Modal
function showEditSubjectModal() {
    document.getElementById('editSubjectModal').classList.remove('hidden');
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Reset Form Fields
function resetForm(formId) {
    document.getElementById(formId).reset();
}

// Submit form functions
function submitAddForm() {
    const form = document.getElementById('addSubjectForm');
    if (form.checkValidity()) {
        form.submit();
        setTimeout(() => {
            resetForm('addSubjectForm');
            closeModal('addSubjectModal');
        }, 100);
    } else {
        form.reportValidity();
    }
}

function submitEditForm() {
    const form = document.getElementById('editSubjectForm');
    if (form.checkValidity()) {
        form.submit();
        setTimeout(() => {
            closeModal('editSubjectModal');
        }, 100);
    } else {
        form.reportValidity();
    }
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modals = ['addSubjectModal', 'editSubjectModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modalId);
            }
        });
    });
 });
 </script>

<!-- Add Subject Modal -->
<div id="addSubjectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-plus mr-2 text-primary-600"></i>
                Add New Subject
            </h3>
            <button onclick="closeModal('addSubjectModal')" class="text-grey-400 hover:text-grey-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="addSubjectForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/subjects/add" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="subject_code" class="block text-sm font-medium text-grey-700 mb-2">Subject Code *</label>
                        <input type="text" id="subject_code" name="subject_code" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="e.g., CS101">
                    </div>
                    <div>
                        <label for="subject_name" class="block text-sm font-medium text-grey-700 mb-2">Subject Name *</label>
                        <input type="text" id="subject_name" name="subject_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="e.g., Introduction to Computer Science">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-grey-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Brief description of the subject"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="units" class="block text-sm font-medium text-grey-700 mb-2">Units *</label>
                        <input type="number" id="units" name="units" required min="1" max="6" value="3"
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="year_level" class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select id="year_level" name="year_level" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Year Level</option>
                            <?php foreach ($yearLevels as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="semester" class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select id="semester" name="semester" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('addSubjectModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitAddForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Add Subject
            </button>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div id="editSubjectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-edit mr-2 text-primary-600"></i>
                Edit Subject
            </h3>
            <button onclick="closeModal('editSubjectModal')" class="text-grey-400 hover:text-grey-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="editSubjectForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/subjects/edit" method="POST">
                <input type="hidden" id="editSubjectId" name="subject_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_subject_code" class="block text-sm font-medium text-grey-700 mb-2">Subject Code *</label>
                        <input type="text" id="edit_subject_code" name="subject_code" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="edit_subject_name" class="block text-sm font-medium text-grey-700 mb-2">Subject Name *</label>
                        <input type="text" id="edit_subject_name" name="subject_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="edit_description" class="block text-sm font-medium text-grey-700 mb-2">Description</label>
                    <textarea id="edit_description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="edit_units" class="block text-sm font-medium text-grey-700 mb-2">Units *</label>
                        <input type="number" id="edit_units" name="units" required min="1" max="6"
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="edit_year_level" class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select id="edit_year_level" name="year_level" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Year Level</option>
                            <?php foreach ($yearLevels as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="edit_semester" class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select id="edit_semester" name="semester" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('editSubjectModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitEditForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Update Subject
            </button>
        </div>
    </div>
</div>
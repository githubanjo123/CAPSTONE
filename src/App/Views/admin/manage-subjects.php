<!-- Top Section - Add Subject Actions -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h4 class="text-xl font-semibold text-grey-800 mb-1">
                <i class="fas fa-book mr-2 text-primary-600"></i>
                Add New Subjects
            </h4>
            <p class="text-grey-600">Add subjects to the system</p>
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
<div class="mb-8">
    <div class="bg-grey-50 p-6 rounded-lg border border-grey-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-grey-700 mb-2">
                    <i class="fas fa-search mr-2"></i>
                    Search Subjects
                </label>
                <input type="text" id="subjectSearch" placeholder="Search by code, name, or description..." 
                       class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Year Level Filter -->
            <div>
                <label class="block text-sm font-medium text-grey-700 mb-2">
                    <i class="fas fa-filter mr-2"></i>
                    Year Level
                </label>
                <select id="yearLevelFilter" class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Year Levels</option>
                    <?php foreach ($yearLevels as $key => $value): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Semester Filter -->
            <div>
                <label class="block text-sm font-medium text-grey-700 mb-2">
                    <i class="fas fa-calendar mr-2"></i>
                    Semester
                </label>
                <select id="semesterFilter" class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Semesters</option>
                    <?php foreach ($semesters as $key => $value): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Clear Filters -->
            <div class="flex items-end">
                <button onclick="clearFilters()" class="w-full bg-grey-500 hover:bg-grey-600 text-white px-4 py-2 rounded-lg transition-all duration-300">
                    <i class="fas fa-times mr-2"></i>
                    Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Subjects Section - Organized by Year & Semester -->
<div class="mb-8">
    <h5 class="text-lg font-semibold text-grey-800 mb-4">
        <i class="fas fa-book-open mr-2 text-primary-600"></i>
        Subjects by Year & Semester
    </h5>
    
    <!-- Year-Semester Tabs -->
    <div class="flex flex-wrap gap-2 mb-6" id="yearSemesterTabs">
        <!-- Tabs will be dynamically generated -->
    </div>

    <!-- Subjects Content -->
    <div id="subjectsContent">
        <!-- Content will be dynamically loaded -->
    </div>
</div>

<!-- Add Subject Modal -->
<div id="addSubjectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="bg-primary-600 text-white px-6 py-4 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Subject
                    </h3>
                    <button onclick="hideAddSubjectModal()" class="text-white hover:text-grey-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form id="addSubjectForm" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject Code *</label>
                        <input type="text" name="subject_code" required 
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="e.g., CS101">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject Name *</label>
                        <input type="text" name="subject_name" required 
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="e.g., Introduction to Computer Science">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Subject description..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Units *</label>
                        <input type="number" name="units" required min="1" max="6" value="3"
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select name="year_level" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Year Level</option>
                            <?php foreach ($yearLevels as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select name="semester" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideAddSubjectModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Add Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div id="editSubjectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="bg-primary-600 text-white px-6 py-4 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Subject
                    </h3>
                    <button onclick="hideEditSubjectModal()" class="text-white hover:text-grey-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form id="editSubjectForm" class="p-6">
                <input type="hidden" name="subject_id" id="editSubjectId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject Code *</label>
                        <input type="text" name="subject_code" id="editSubjectCode" required 
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject Name *</label>
                        <input type="text" name="subject_name" id="editSubjectName" required 
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Description</label>
                        <textarea name="description" id="editSubjectDescription" rows="3" 
                                  class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Units *</label>
                        <input type="number" name="units" id="editSubjectUnits" required min="1" max="6"
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select name="year_level" id="editSubjectYearLevel" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Year Level</option>
                            <?php foreach ($yearLevels as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select name="semester" id="editSubjectSemester" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideEditSubjectModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Update Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteSubjectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="bg-red-600 text-white px-6 py-4 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Confirm Delete
                    </h3>
                    <button onclick="hideDeleteSubjectModal()" class="text-white hover:text-grey-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <p class="text-grey-700 mb-4">Are you sure you want to delete this subject?</p>
                <p class="text-sm text-grey-500 mb-6">This action cannot be undone.</p>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="hideDeleteSubjectModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button onclick="confirmDeleteSubject()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-trash mr-2"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentSubjects = <?= json_encode($subjects) ?>;
let currentDeleteSubjectId = null;

// Initialize subjects display
document.addEventListener('DOMContentLoaded', function() {
    loadSubjects();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Search functionality
    document.getElementById('subjectSearch').addEventListener('input', function() {
        filterSubjects();
    });
    
    // Filter functionality
    document.getElementById('yearLevelFilter').addEventListener('change', function() {
        filterSubjects();
    });
    
    document.getElementById('semesterFilter').addEventListener('change', function() {
        filterSubjects();
    });
    
    // Form submissions
    document.getElementById('addSubjectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addSubject();
    });
    
    document.getElementById('editSubjectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateSubject();
    });
}

// Load and display subjects
function loadSubjects() {
    const subjects = currentSubjects;
    const yearSemesterGroups = groupSubjectsByYearSemester(subjects);
    
    // Generate tabs
    const tabsContainer = document.getElementById('yearSemesterTabs');
    tabsContainer.innerHTML = '';
    
    let firstTab = true;
    Object.keys(yearSemesterGroups).forEach(yearSemester => {
        const count = yearSemesterGroups[yearSemester].length;
        const tabId = 'tab-' + yearSemester.replace(/\s+/g, '-').toLowerCase();
        
        const tab = document.createElement('button');
        tab.className = `year-semester-tab ${firstTab ? 'active' : ''} bg-grey-100 border border-grey-300 text-grey-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300 hover:bg-primary-600 hover:text-white hover:border-primary-600`;
        tab.setAttribute('data-section', tabId);
        tab.innerHTML = `${yearSemester} <span class="bg-green-500 text-white rounded-full w-5 h-5 inline-flex items-center justify-center text-xs font-bold ml-2">${count}</span>`;
        
        tab.addEventListener('click', function() {
            showYearSemesterSection(tabId);
        });
        
        tabsContainer.appendChild(tab);
        firstTab = false;
    });
    
    // Generate content
    const contentContainer = document.getElementById('subjectsContent');
    contentContainer.innerHTML = '';
    
    let firstSection = true;
    Object.keys(yearSemesterGroups).forEach(yearSemester => {
        const subjects = yearSemesterGroups[yearSemester];
        const sectionId = 'tab-' + yearSemester.replace(/\s+/g, '-').toLowerCase();
        
        const section = document.createElement('div');
        section.className = `year-semester-section ${firstSection ? 'active' : ''} ${!firstSection ? 'hidden' : ''}`;
        section.id = sectionId;
        
        section.innerHTML = generateSubjectsSectionHTML(yearSemester, subjects);
        contentContainer.appendChild(section);
        firstSection = false;
    });
}

// Group subjects by year level and semester
function groupSubjectsByYearSemester(subjects) {
    const groups = {};
    subjects.forEach(subject => {
        const key = `${subject.year_level} - ${subject.semester}`;
        if (!groups[key]) {
            groups[key] = [];
        }
        groups[key].push(subject);
    });
    return groups;
}

// Generate HTML for subjects section
function generateSubjectsSectionHTML(yearSemester, subjects) {
    let html = `
        <div class="bg-grey-100 p-4 rounded-lg mb-4 border-l-4 border-primary-600">
            <div class="flex justify-between items-center">
                <div>
                    <h6 class="text-lg font-bold text-primary-600">
                        <i class="fas fa-book mr-2"></i>
                        ${yearSemester}
                    </h6>
                </div>
                <div>
                    <span class="text-grey-600 text-sm">${subjects.length} subjects</span>
                </div>
            </div>
        </div>
    `;
    
    subjects.forEach(subject => {
        html += `
            <div class="bg-white border border-grey-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <div class="text-lg font-bold text-primary-600 mb-2">
                            <i class="fas fa-book mr-2"></i>
                            ${escapeHtml(subject.subject_name)}
                        </div>
                        <div class="text-grey-600 text-sm mb-2">
                            <i class="fas fa-code mr-2"></i>
                            ${escapeHtml(subject.subject_code)}
                        </div>
                        ${subject.description ? `
                        <div class="text-grey-500 text-sm mb-2">
                            <i class="fas fa-info-circle mr-2"></i>
                            ${escapeHtml(subject.description)}
                        </div>
                        ` : ''}
                        <div class="text-grey-500 text-xs">
                            <i class="fas fa-calendar mr-2"></i>
                            ${subject.units} units • Added on ${formatDate(subject.created_at)}
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="editSubject(${subject.subject_id})">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </button>
                        <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="deleteSubject(${subject.subject_id})">
                            <i class="fas fa-trash mr-1"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    return html;
}

// Show year-semester section
function showYearSemesterSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.year-semester-section').forEach(section => {
        section.classList.add('hidden');
        section.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.year-semester-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected section
    const selectedSection = document.getElementById(sectionId);
    selectedSection.classList.remove('hidden');
    selectedSection.classList.add('active');
    
    // Add active class to selected tab
    const activeTab = document.querySelector(`[data-section="${sectionId}"]`);
    activeTab.classList.add('active');
}

// Filter subjects
function filterSubjects() {
    const searchTerm = document.getElementById('subjectSearch').value.toLowerCase();
    const yearLevel = document.getElementById('yearLevelFilter').value;
    const semester = document.getElementById('semesterFilter').value;
    
    let filteredSubjects = currentSubjects.filter(subject => {
        const matchesSearch = !searchTerm || 
            subject.subject_code.toLowerCase().includes(searchTerm) ||
            subject.subject_name.toLowerCase().includes(searchTerm) ||
            (subject.description && subject.description.toLowerCase().includes(searchTerm));
        
        const matchesYearLevel = !yearLevel || subject.year_level === yearLevel;
        const matchesSemester = !semester || subject.semester === semester;
        
        return matchesSearch && matchesYearLevel && matchesSemester;
    });
    
    // Update display with filtered subjects
    const yearSemesterGroups = groupSubjectsByYearSemester(filteredSubjects);
    
    // Update tabs
    const tabsContainer = document.getElementById('yearSemesterTabs');
    tabsContainer.innerHTML = '';
    
    let firstTab = true;
    Object.keys(yearSemesterGroups).forEach(yearSemester => {
        const count = yearSemesterGroups[yearSemester].length;
        const tabId = 'tab-' + yearSemester.replace(/\s+/g, '-').toLowerCase();
        
        const tab = document.createElement('button');
        tab.className = `year-semester-tab ${firstTab ? 'active' : ''} bg-grey-100 border border-grey-300 text-grey-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300 hover:bg-primary-600 hover:text-white hover:border-primary-600`;
        tab.setAttribute('data-section', tabId);
        tab.innerHTML = `${yearSemester} <span class="bg-green-500 text-white rounded-full w-5 h-5 inline-flex items-center justify-center text-xs font-bold ml-2">${count}</span>`;
        
        tab.addEventListener('click', function() {
            showYearSemesterSection(tabId);
        });
        
        tabsContainer.appendChild(tab);
        firstTab = false;
    });
    
    // Update content
    const contentContainer = document.getElementById('subjectsContent');
    contentContainer.innerHTML = '';
    
    let firstSection = true;
    Object.keys(yearSemesterGroups).forEach(yearSemester => {
        const subjects = yearSemesterGroups[yearSemester];
        const sectionId = 'tab-' + yearSemester.replace(/\s+/g, '-').toLowerCase();
        
        const section = document.createElement('div');
        section.className = `year-semester-section ${firstSection ? 'active' : ''} ${!firstSection ? 'hidden' : ''}`;
        section.id = sectionId;
        
        section.innerHTML = generateSubjectsSectionHTML(yearSemester, subjects);
        contentContainer.appendChild(section);
        firstSection = false;
    });
}

// Clear filters
function clearFilters() {
    document.getElementById('subjectSearch').value = '';
    document.getElementById('yearLevelFilter').value = '';
    document.getElementById('semesterFilter').value = '';
    loadSubjects();
}

// Modal functions
function showAddSubjectModal() {
    document.getElementById('addSubjectModal').classList.remove('hidden');
    document.getElementById('addSubjectForm').reset();
}

function hideAddSubjectModal() {
    document.getElementById('addSubjectModal').classList.add('hidden');
}

function showEditSubjectModal() {
    document.getElementById('editSubjectModal').classList.remove('hidden');
}

function hideEditSubjectModal() {
    document.getElementById('editSubjectModal').classList.add('hidden');
}

function showDeleteSubjectModal() {
    document.getElementById('deleteSubjectModal').classList.remove('hidden');
}

function hideDeleteSubjectModal() {
    document.getElementById('deleteSubjectModal').classList.add('hidden');
    currentDeleteSubjectId = null;
}

// CRUD operations
function addSubject() {
    const formData = new FormData(document.getElementById('addSubjectForm'));
    
    fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/subjects/add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddSubjectModal();
            location.reload(); // Reload to show new subject
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the subject.');
    });
}

function editSubject(subjectId) {
    // Fetch subject data
    fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/subjects/${subjectId}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const subject = data.data;
            
            // Populate form
            document.getElementById('editSubjectId').value = subject.subject_id;
            document.getElementById('editSubjectCode').value = subject.subject_code;
            document.getElementById('editSubjectName').value = subject.subject_name;
            document.getElementById('editSubjectDescription').value = subject.description || '';
            document.getElementById('editSubjectUnits').value = subject.units;
            document.getElementById('editSubjectYearLevel').value = subject.year_level;
            document.getElementById('editSubjectSemester').value = subject.semester;
            
            showEditSubjectModal();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fetching subject data.');
    });
}

function updateSubject() {
    const formData = new FormData(document.getElementById('editSubjectForm'));
    
    fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/subjects/edit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideEditSubjectModal();
            location.reload(); // Reload to show updated subject
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the subject.');
    });
}

function deleteSubject(subjectId) {
    currentDeleteSubjectId = subjectId;
    showDeleteSubjectModal();
}

function confirmDeleteSubject() {
    if (!currentDeleteSubjectId) return;
    
    const formData = new FormData();
    formData.append('subject_id', currentDeleteSubjectId);
    
    fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/subjects/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideDeleteSubjectModal();
            location.reload(); // Reload to show updated list
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the subject.');
    });
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}
</script>
<!-- Top Section - Add Assignment Actions -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h4 class="text-xl font-semibold text-grey-800 mb-1">
                <i class="fas fa-link mr-2 text-primary-600"></i>
                Subject Assignments
            </h4>
            <p class="text-grey-600">Manage faculty assignments to subjects by year level and section</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1" onclick="showAddAssignmentModal()">
                <i class="fas fa-plus mr-2"></i>
                Add Assignment
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md border border-grey-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-link text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-grey-600">Total Assignments</p>
                <p class="text-2xl font-semibold text-grey-900" id="totalAssignments">0</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md border border-grey-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-grey-600">Active</p>
                <p class="text-2xl font-semibold text-grey-900" id="activeAssignments">0</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md border border-grey-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-grey-600">Pending</p>
                <p class="text-2xl font-semibold text-grey-900" id="pendingAssignments">0</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md border border-grey-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-grey-600">Unassigned</p>
                <p class="text-2xl font-semibold text-grey-900" id="unassignedSubjects">0</p>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="mb-8">
    <div class="bg-grey-50 p-6 rounded-lg border border-grey-200">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-grey-700 mb-2">
                    <i class="fas fa-search mr-2"></i>
                    Search Assignments
                </label>
                <input type="text" id="assignmentSearch" placeholder="Search assignments..." 
                       class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Academic Year Filter -->
            <div>
                <label class="block text-sm font-medium text-grey-700 mb-2">
                    <i class="fas fa-calendar mr-2"></i>
                    Academic Year
                </label>
                <select id="academicYearFilter" class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Years</option>
                    <?php foreach ($academicYears as $key => $value): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Semester Filter -->
            <div>
                <label class="block text-sm font-medium text-grey-700 mb-2">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Semester
                </label>
                <select id="semesterFilter" class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Semesters</option>
                    <?php foreach ($assignmentSemesters as $key => $value): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-grey-700 mb-2">
                    <i class="fas fa-filter mr-2"></i>
                    Status
                </label>
                <select id="statusFilter" class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Status</option>
                    <?php foreach ($assignmentStatuses as $key => $value): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Clear Filters -->
            <div class="flex items-end">
                <button onclick="clearAssignmentFilters()" class="w-full bg-grey-500 hover:bg-grey-600 text-white px-4 py-2 rounded-lg transition-all duration-300">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Assignments Table -->
<div class="bg-white rounded-lg shadow-md border border-grey-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-grey-200">
        <h5 class="text-lg font-semibold text-grey-800">
            <i class="fas fa-list mr-2"></i>
            Assignment List
        </h5>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-grey-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Faculty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Year & Section</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Academic Year</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Semester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="assignmentsTableBody" class="bg-white divide-y divide-grey-200">
                <!-- Assignments will be loaded here dynamically -->
            </tbody>
        </table>
    </div>
    
    <!-- Empty State -->
    <div id="assignmentsEmptyState" class="hidden text-center py-12">
        <i class="fas fa-link text-6xl text-grey-400 mb-4"></i>
        <h4 class="text-xl font-semibold text-grey-700 mb-2">No Assignments Found</h4>
        <p class="text-grey-500 mb-6">Start by adding your first subject assignment.</p>
        <button onclick="showAddAssignmentModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300">
            <i class="fas fa-plus mr-2"></i>
            Add First Assignment
        </button>
    </div>
</div>

<!-- Add Assignment Modal -->
<div id="addAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl">
            <div class="bg-primary-600 text-white px-6 py-4 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Assignment
                    </h3>
                    <button onclick="hideAddAssignmentModal()" class="text-white hover:text-grey-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form id="addAssignmentForm" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Subject Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject *</label>
                        <select name="subject_id" id="assignmentSubject" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject->getSubjectId() ?>">
                                    <?= htmlspecialchars($subject->getSubjectCode() . ' - ' . $subject->getSubjectName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Faculty Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Faculty Member *</label>
                        <select name="faculty_id" id="assignmentFaculty" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Faculty Member</option>
                            <?php foreach ($faculty as $facultyMember): ?>
                                <option value="<?= $facultyMember['user_id'] ?>">
                                    <?= htmlspecialchars($facultyMember['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Year Level -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select name="year_level" id="assignmentYearLevel" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Year Level</option>
                            <?php foreach ($assignmentYearLevels as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Section -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select name="section" id="assignmentSection" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Section</option>
                            <?php foreach ($assignmentSections as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Academic Year -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Academic Year *</label>
                        <select name="academic_year" id="assignmentAcademicYear" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Academic Year</option>
                            <?php foreach ($academicYears as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Semester -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select name="semester" id="assignmentSemester" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Semester</option>
                            <?php foreach ($assignmentSemesters as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Status</label>
                        <select name="status" id="assignmentStatus" 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <?php foreach ($assignmentStatuses as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>" <?= $key === 'active' ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($value) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Notes</label>
                        <textarea name="notes" id="assignmentNotes" rows="3" 
                                  class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Additional notes about this assignment..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideAddAssignmentModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Add Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div id="editAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl">
            <div class="bg-primary-600 text-white px-6 py-4 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Assignment
                    </h3>
                    <button onclick="hideEditAssignmentModal()" class="text-white hover:text-grey-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form id="editAssignmentForm" class="p-6">
                <input type="hidden" name="assignment_id" id="editAssignmentId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Subject Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject *</label>
                        <select name="subject_id" id="editAssignmentSubject" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject->getSubjectId() ?>">
                                    <?= htmlspecialchars($subject->getSubjectCode() . ' - ' . $subject->getSubjectName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Faculty Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Faculty Member *</label>
                        <select name="faculty_id" id="editAssignmentFaculty" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Faculty Member</option>
                            <?php foreach ($faculty as $facultyMember): ?>
                                <option value="<?= $facultyMember['user_id'] ?>">
                                    <?= htmlspecialchars($facultyMember['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Year Level -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select name="year_level" id="editAssignmentYearLevel" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Year Level</option>
                            <?php foreach ($assignmentYearLevels as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Section -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select name="section" id="editAssignmentSection" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Section</option>
                            <?php foreach ($assignmentSections as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Academic Year -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Academic Year *</label>
                        <select name="academic_year" id="editAssignmentAcademicYear" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Academic Year</option>
                            <?php foreach ($academicYears as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Semester -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select name="semester" id="editAssignmentSemester" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Semester</option>
                            <?php foreach ($assignmentSemesters as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Status</label>
                        <select name="status" id="editAssignmentStatus" 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <?php foreach ($assignmentStatuses as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Notes</label>
                        <textarea name="notes" id="editAssignmentNotes" rows="3" 
                                  class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Additional notes about this assignment..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideEditAssignmentModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="bg-red-600 text-white px-6 py-4 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Confirm Delete
                    </h3>
                    <button onclick="hideDeleteAssignmentModal()" class="text-white hover:text-grey-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <p class="text-grey-700 mb-4">Are you sure you want to delete this assignment?</p>
                <p class="text-sm text-grey-500 mb-6">This action cannot be undone.</p>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="hideDeleteAssignmentModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button onclick="confirmDeleteAssignment()" 
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
let currentAssignments = <?= json_encode($assignments ?? []) ?>;
let currentDeleteAssignmentId = null;

// Initialize assignments display
document.addEventListener('DOMContentLoaded', function() {
    loadAssignments();
    loadAssignmentStats();
    setupAssignmentEventListeners();
});

// Setup event listeners
function setupAssignmentEventListeners() {
    // Search functionality
    document.getElementById('assignmentSearch').addEventListener('input', function() {
        filterAssignments();
    });
    
    // Filter functionality
    document.getElementById('academicYearFilter').addEventListener('change', function() {
        filterAssignments();
    });
    
    document.getElementById('semesterFilter').addEventListener('change', function() {
        filterAssignments();
    });
    
    document.getElementById('statusFilter').addEventListener('change', function() {
        filterAssignments();
    });
    
    // Form submissions
    document.getElementById('addAssignmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addAssignment();
    });
    
    document.getElementById('editAssignmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateAssignment();
    });
}

// Load and display assignments
function loadAssignments() {
    const tableBody = document.getElementById('assignmentsTableBody');
    const emptyState = document.getElementById('assignmentsEmptyState');
    
    if (currentAssignments.length === 0) {
        tableBody.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    let html = '';
    currentAssignments.forEach(assignment => {
        const statusClass = getStatusClass(assignment.status);
        const statusIcon = getStatusIcon(assignment.status);
        
        html += `
            <tr class="hover:bg-grey-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                        <div class="text-sm font-medium text-grey-900">
                            ${escapeHtml(assignment.subject_code || '')} - ${escapeHtml(assignment.subject_name || '')}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-grey-900">${escapeHtml(assignment.faculty_name || '')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-grey-900">${escapeHtml(assignment.year_level || '')} - ${escapeHtml(assignment.section || '')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-grey-900">${escapeHtml(assignment.academic_year || '')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-grey-900">${escapeHtml(assignment.semester || '')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                        <i class="${statusIcon} mr-1"></i>
                        ${escapeHtml(assignment.status || '')}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        <button class="text-indigo-600 hover:text-indigo-900" onclick="editAssignment(${assignment.id || 0})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900" onclick="deleteAssignment(${assignment.id || 0})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

// Get status class for styling
function getStatusClass(status) {
    if (!status) return 'bg-grey-100 text-grey-800';
    switch (status) {
        case 'active': return 'bg-green-100 text-green-800';
        case 'inactive': return 'bg-red-100 text-red-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-grey-100 text-grey-800';
    }
}

// Get status icon
function getStatusIcon(status) {
    if (!status) return 'fas fa-question-circle';
    switch (status) {
        case 'active': return 'fas fa-check-circle';
        case 'inactive': return 'fas fa-times-circle';
        case 'pending': return 'fas fa-clock';
        default: return 'fas fa-question-circle';
    }
}

// Filter assignments
function filterAssignments() {
    const searchTerm = document.getElementById('assignmentSearch').value.toLowerCase();
    const academicYear = document.getElementById('academicYearFilter').value;
    const semester = document.getElementById('semesterFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    let filteredAssignments = currentAssignments.filter(assignment => {
        const matchesSearch = !searchTerm || 
            (assignment.subject_code && assignment.subject_code.toLowerCase().includes(searchTerm)) ||
            (assignment.subject_name && assignment.subject_name.toLowerCase().includes(searchTerm)) ||
            (assignment.faculty_name && assignment.faculty_name.toLowerCase().includes(searchTerm)) ||
            (assignment.notes && assignment.notes.toLowerCase().includes(searchTerm));
        
        const matchesAcademicYear = !academicYear || assignment.academic_year === academicYear;
        const matchesSemester = !semester || assignment.semester === semester;
        const matchesStatus = !status || assignment.status === status;
        
        return matchesSearch && matchesAcademicYear && matchesSemester && matchesStatus;
    });
    
    // Update display with filtered assignments
    const tableBody = document.getElementById('assignmentsTableBody');
    const emptyState = document.getElementById('assignmentsEmptyState');
    
    if (filteredAssignments.length === 0) {
        tableBody.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    let html = '';
    filteredAssignments.forEach(assignment => {
        const statusClass = getStatusClass(assignment.status);
        const statusIcon = getStatusIcon(assignment.status);
        
        html += `
            <tr class="hover:bg-grey-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                        <div class="text-sm font-medium text-grey-900">
                            ${escapeHtml(assignment.subject_code || '')} - ${escapeHtml(assignment.subject_name || '')}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-grey-900">${escapeHtml(assignment.faculty_name || '')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-grey-900">${escapeHtml(assignment.year_level || '')} - ${escapeHtml(assignment.section || '')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-grey-900">${escapeHtml(assignment.academic_year || '')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-grey-900">${escapeHtml(assignment.semester || '')}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                        <i class="${statusIcon} mr-1"></i>
                        ${escapeHtml(assignment.status || '')}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        <button class="text-indigo-600 hover:text-indigo-900" onclick="editAssignment(${assignment.id || 0})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900" onclick="deleteAssignment(${assignment.id || 0})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

// Clear filters
function clearAssignmentFilters() {
    document.getElementById('assignmentSearch').value = '';
    document.getElementById('academicYearFilter').value = '';
    document.getElementById('semesterFilter').value = '';
    document.getElementById('statusFilter').value = '';
    loadAssignments();
}

// Load assignment statistics
function loadAssignmentStats() {
    fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/assignments/stats')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            const stats = data.data || {};
            document.getElementById('totalAssignments').textContent = stats.total_assignments || 0;
            document.getElementById('activeAssignments').textContent = stats.active_assignments || 0;
            document.getElementById('pendingAssignments').textContent = stats.pending_assignments || 0;
            
            // Calculate unassigned subjects (this would need to be implemented)
            document.getElementById('unassignedSubjects').textContent = '0';
        } else {
            console.error('Error loading assignment stats:', data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Error loading assignment stats:', error);
        // Set default values on error
        document.getElementById('totalAssignments').textContent = '0';
        document.getElementById('activeAssignments').textContent = '0';
        document.getElementById('pendingAssignments').textContent = '0';
        document.getElementById('unassignedSubjects').textContent = '0';
    });
}

// Modal functions
function showAddAssignmentModal() {
    document.getElementById('addAssignmentModal').classList.remove('hidden');
    document.getElementById('addAssignmentForm').reset();
}

function hideAddAssignmentModal() {
    document.getElementById('addAssignmentModal').classList.add('hidden');
}

function showEditAssignmentModal() {
    document.getElementById('editAssignmentModal').classList.remove('hidden');
}

function hideEditAssignmentModal() {
    document.getElementById('editAssignmentModal').classList.add('hidden');
}

function showDeleteAssignmentModal() {
    document.getElementById('deleteAssignmentModal').classList.remove('hidden');
}

function hideDeleteAssignmentModal() {
    document.getElementById('deleteAssignmentModal').classList.add('hidden');
    currentDeleteAssignmentId = null;
}

// CRUD operations
function addAssignment() {
    const formData = new FormData(document.getElementById('addAssignmentForm'));
    
    fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/assignments/add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddAssignmentModal();
            refreshAssignmentsData();
            showSuccessMessage('Assignment added successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the assignment.');
    });
}

function editAssignment(assignmentId) {
    // Fetch assignment data
    fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/assignments/${assignmentId}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const assignment = data.data;
            
            // Populate form
            document.getElementById('editAssignmentId').value = assignment.id;
            document.getElementById('editAssignmentSubject').value = assignment.subject_id;
            document.getElementById('editAssignmentFaculty').value = assignment.faculty_id;
            document.getElementById('editAssignmentYearLevel').value = assignment.year_level;
            document.getElementById('editAssignmentSection').value = assignment.section;
            document.getElementById('editAssignmentAcademicYear').value = assignment.academic_year;
            document.getElementById('editAssignmentSemester').value = assignment.semester;
            document.getElementById('editAssignmentStatus').value = assignment.status;
            document.getElementById('editAssignmentNotes').value = assignment.notes || '';
            
            showEditAssignmentModal();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fetching assignment data.');
    });
}

function updateAssignment() {
    const formData = new FormData(document.getElementById('editAssignmentForm'));
    
    fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/assignments/edit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideEditAssignmentModal();
            refreshAssignmentsData();
            showSuccessMessage('Assignment updated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the assignment.');
    });
}

function deleteAssignment(assignmentId) {
    currentDeleteAssignmentId = assignmentId;
    showDeleteAssignmentModal();
}

function confirmDeleteAssignment() {
    if (!currentDeleteAssignmentId) return;
    
    const formData = new FormData();
    formData.append('assignment_id', currentDeleteAssignmentId);
    
    fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/assignments/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideDeleteAssignmentModal();
            refreshAssignmentsData();
            showSuccessMessage('Assignment deleted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the assignment.');
    });
}

// Helper functions for dynamic updates
function refreshAssignmentsData() {
    // Fetch fresh assignments data from the server
    fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/assignments/refresh')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            currentAssignments = data.data || [];
            loadAssignments();
            loadAssignmentStats();
        } else {
            console.error('Error refreshing assignments:', data.message || 'Unknown error');
            currentAssignments = [];
            loadAssignments();
        }
    })
    .catch(error => {
        console.error('Error refreshing assignments:', error);
        currentAssignments = [];
        loadAssignments();
    });
}

function showSuccessMessage(message) {
    // Create a temporary success message
    const successDiv = document.createElement('div');
    successDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center';
    successDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            ${message}
        </div>
        <button type="button" class="text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Insert at the top of the assignments content area
    const assignmentsContent = document.getElementById('assignments');
    assignmentsContent.insertBefore(successDiv, assignmentsContent.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (successDiv.parentElement) {
            successDiv.remove();
        }
    }, 5000);
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
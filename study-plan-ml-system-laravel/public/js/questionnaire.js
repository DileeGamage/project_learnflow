// Standalone questionnaire.js file
document.addEventListener('DOMContentLoaded', function() {
    console.log('Standalone questionnaire.js loaded');
    
    // Find the questionnaire elements if they exist
    const questionnaireSections = document.querySelectorAll('.question-section');
    
    // If no questionnaire on this page, exit early
    if (questionnaireSections.length === 0) {
        console.log('No questionnaire found on this page');
        return;
    }
    
    console.log('Questionnaire found with ' + questionnaireSections.length + ' sections');
    
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const submitBtn = document.querySelector('.submit-btn');
    const progressBar = document.getElementById('progress-bar');
    
    console.log('Navigation buttons found:', {
        prev: !!prevBtn,
        next: !!nextBtn,
        submit: !!submitBtn
    });
    
    let currentQuestion = 1;
    const totalQuestions = questionnaireSections.length;
    
    // Update progress bar
    function updateProgress() {
        if (!progressBar) return;
        const progress = (currentQuestion - 1) / totalQuestions * 100;
        progressBar.style.width = `${progress}%`;
    }
    
    // Show a specific question section
    function showQuestion(questionNum) {
        console.log('Showing question:', questionNum);
        
        questionnaireSections.forEach(section => {
            section.style.display = 'none';
        });
        
        const questionToShow = document.querySelector(`[data-question="${questionNum}"]`);
        if (questionToShow) {
            questionToShow.style.display = 'block';
        } else {
            console.error(`Question ${questionNum} not found!`);
        }
        
        // Update button visibility
        if (prevBtn) prevBtn.style.display = questionNum > 1 ? 'block' : 'none';
        if (nextBtn) nextBtn.style.display = questionNum < totalQuestions ? 'block' : 'none';
        if (submitBtn) submitBtn.style.display = questionNum === totalQuestions ? 'block' : 'none';
        
        currentQuestion = questionNum;
        updateProgress();
    }
    
    // Add event listeners for navigation buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            console.log('Previous button clicked');
            if (currentQuestion > 1) {
                showQuestion(currentQuestion - 1);
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            console.log('Next button clicked');
            
            // Simple validation - check if current question has an answer
            const currentSection = document.querySelector(`[data-question="${currentQuestion}"]`);
            const inputs = currentSection.querySelectorAll('input[type="radio"]');
            let answered = false;
            
            inputs.forEach(input => {
                if (input.checked) answered = true;
            });
            
            if (!answered) {
                alert('Please select an answer before continuing.');
                return;
            }
            
            if (currentQuestion < totalQuestions) {
                showQuestion(currentQuestion + 1);
            }
        });
    }
    
    // Initialize the first question
    showQuestion(1);
    console.log('Questionnaire initialized from standalone JS file');
});
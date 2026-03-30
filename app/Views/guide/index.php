<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-question-circle me-2"></i>User Guide</h1>
            <p class="text-muted mb-0">Everything you need to know about the Freelance Proposal Optimizer</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10 col-xl-8">
            <div class="accordion accordion-flush" id="guideAccordion">

                <!-- Getting Started -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingStarted">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStarted" aria-expanded="true" aria-controls="collapseStarted">
                            <i class="bi bi-rocket-takeoff me-2 text-primary"></i>
                            <strong>Getting Started</strong>
                        </button>
                    </h2>
                    <div id="collapseStarted" class="accordion-collapse collapse show" aria-labelledby="headingStarted" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <h6><i class="bi bi-box-arrow-in-right me-1"></i> Logging In</h6>
                            <p>Navigate to the login page and enter your credentials. If you don't have an account, use the registration form to create one. You can also reset your password via the "Forgot Password" link.</p>

                            <h6><i class="bi bi-speedometer2 me-1"></i> Dashboard Overview</h6>
                            <p>The dashboard is your home base. It provides a quick snapshot of your activity:</p>
                            <ul>
                                <li><strong>Active Jobs</strong> &mdash; Jobs you've added that are awaiting proposals</li>
                                <li><strong>Generated Proposals</strong> &mdash; Total proposals created by the AI</li>
                                <li><strong>Submitted Proposals</strong> &mdash; Proposals you've finalized and marked as sent</li>
                                <li><strong>Active Resume</strong> &mdash; The resume currently used for proposal generation</li>
                            </ul>
                            <p>Use the left sidebar to navigate between sections. Each section is described in detail below.</p>
                        </div>
                    </div>
                </div>

                <!-- Managing Resumes -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingResumes">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseResumes" aria-expanded="false" aria-controls="collapseResumes">
                            <i class="bi bi-file-person me-2 text-success"></i>
                            <strong>Managing Resumes</strong>
                        </button>
                    </h2>
                    <div id="collapseResumes" class="accordion-collapse collapse" aria-labelledby="headingResumes" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <h6><i class="bi bi-upload me-1"></i> Uploading a Resume</h6>
                            <p>Go to <strong>Resumes</strong> in the sidebar and click <strong>"Add Resume"</strong>. You can paste your resume content directly into the text area, or upload a file. The system will parse the content and store it for use during proposal generation.</p>

                            <h6><i class="bi bi-check-circle me-1"></i> Activating a Resume</h6>
                            <p>You can store multiple resumes (e.g., one for web development, another for design). Only one resume is <strong>active</strong> at a time. Click the <strong>"Activate"</strong> button next to a resume to make it the one used for AI-generated proposals.</p>

                            <h6><i class="bi bi-pencil me-1"></i> Editing a Resume</h6>
                            <p>Click <strong>"Edit"</strong> on any resume to update its title or content. Keep your resume current so the AI has the best information to work with when generating proposals.</p>

                            <div class="alert alert-info mt-3">
                                <i class="bi bi-lightbulb me-1"></i>
                                <strong>Tip:</strong> The more detailed and accurate your resume, the better the AI can match your skills to job requirements.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Managing Talents -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTalents">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTalents" aria-expanded="false" aria-controls="collapseTalents">
                            <i class="bi bi-stars me-2 text-warning"></i>
                            <strong>Managing Talents</strong>
                        </button>
                    </h2>
                    <div id="collapseTalents" class="accordion-collapse collapse" aria-labelledby="headingTalents" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <h6><i class="bi bi-plus-circle me-1"></i> Adding Skills</h6>
                            <p>Your talent profile lists the skills and technologies you're proficient in. These are used during proposal generation to highlight relevant experience and assess job fit.</p>

                            <h6><i class="bi bi-bar-chart me-1"></i> Proficiency Levels</h6>
                            <p>Assign a proficiency level to each skill to help the AI accurately represent your expertise:</p>
                            <ul>
                                <li><strong>Beginner</strong> &mdash; Familiar with basics, limited hands-on experience</li>
                                <li><strong>Intermediate</strong> &mdash; Comfortable working independently with the skill</li>
                                <li><strong>Advanced</strong> &mdash; Deep expertise, can architect solutions and mentor others</li>
                                <li><strong>Expert</strong> &mdash; Industry-leading knowledge, extensive production experience</li>
                            </ul>
                            <p>Skills with higher proficiency are weighted more heavily in fit analysis and are highlighted in proposals.</p>
                        </div>
                    </div>
                </div>

                <!-- Adding Jobs -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingJobs">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseJobs" aria-expanded="false" aria-controls="collapseJobs">
                            <i class="bi bi-briefcase me-2 text-info"></i>
                            <strong>Adding Jobs</strong>
                        </button>
                    </h2>
                    <div id="collapseJobs" class="accordion-collapse collapse" aria-labelledby="headingJobs" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <h6><i class="bi bi-clipboard-plus me-1"></i> Paste a Job Posting</h6>
                            <p>Navigate to <strong>Jobs</strong> and click <strong>"Add Job"</strong>. You can paste the full text of a job posting directly into the description field. Include as much detail as possible &mdash; requirements, responsibilities, budget, and timeline all help the AI write a better proposal.</p>

                            <h6><i class="bi bi-file-earmark-arrow-up me-1"></i> Upload Job Files</h6>
                            <p>Some jobs include attachments or detailed briefs. You can upload files alongside the job description to give the AI additional context.</p>

                            <h6><i class="bi bi-filetype-pdf me-1"></i> Supported Formats</h6>
                            <ul>
                                <li><strong>PDF</strong> &mdash; Job descriptions, project briefs, RFPs</li>
                                <li><strong>TXT</strong> &mdash; Plain text job postings</li>
                                <li><strong>DOC / DOCX</strong> &mdash; Word documents with job details</li>
                            </ul>

                            <h6><i class="bi bi-tag me-1"></i> Job Metadata</h6>
                            <p>Fill in the platform (Upwork, Freelancer, etc.), job URL, budget range, and any other fields to keep your jobs organized and provide context for proposal generation.</p>
                        </div>
                    </div>
                </div>

                <!-- Generating Proposals -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingGenerate">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGenerate" aria-expanded="false" aria-controls="collapseGenerate">
                            <i class="bi bi-magic me-2 text-danger"></i>
                            <strong>Generating Proposals</strong>
                        </button>
                    </h2>
                    <div id="collapseGenerate" class="accordion-collapse collapse" aria-labelledby="headingGenerate" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <h6><i class="bi bi-cpu me-1"></i> How AI Generation Works</h6>
                            <p>When you click <strong>"Generate Proposal"</strong> on a job, the system sends your active resume, talent profile, platform rules, and the job description to the Claude AI. It crafts a tailored cover letter that highlights your relevant experience and addresses the client's specific needs.</p>

                            <h6><i class="bi bi-graph-up me-1"></i> Fit Analysis</h6>
                            <p>Along with each proposal, the AI produces a <strong>fit score</strong> &mdash; a percentage indicating how well your skills and experience match the job requirements. This helps you prioritize which jobs to pursue.</p>
                            <div class="d-flex gap-2 mb-3">
                                <span class="badge bg-success">90-100% Excellent Fit</span>
                                <span class="badge bg-primary">70-89% Good Fit</span>
                                <span class="badge bg-warning text-dark">50-69% Moderate Fit</span>
                                <span class="badge bg-danger">Below 50% Weak Fit</span>
                            </div>

                            <h6><i class="bi bi-exclamation-triangle me-1"></i> Skill Gaps</h6>
                            <p>The AI also identifies <strong>skill gaps</strong> &mdash; requirements in the job posting that don't match your resume or talent profile. Use this information to decide whether to apply, or to address the gaps honestly in your proposal.</p>

                            <h6><i class="bi bi-arrow-repeat me-1"></i> Regenerating</h6>
                            <p>Not satisfied with a proposal? Click <strong>"Regenerate"</strong> to get a fresh version. Each generation may take a slightly different angle or emphasize different aspects of your experience.</p>
                        </div>
                    </div>
                </div>

                <!-- Editing & Submitting -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSubmit">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubmit" aria-expanded="false" aria-controls="collapseSubmit">
                            <i class="bi bi-send me-2 text-primary"></i>
                            <strong>Editing &amp; Submitting Proposals</strong>
                        </button>
                    </h2>
                    <div id="collapseSubmit" class="accordion-collapse collapse" aria-labelledby="headingSubmit" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <h6><i class="bi bi-pencil-square me-1"></i> Edit Before Sending</h6>
                            <p>AI-generated proposals are a starting point. Always review and edit before submitting to a client. Click <strong>"Edit"</strong> on any proposal to customize the tone, add personal anecdotes, or adjust the approach.</p>

                            <h6><i class="bi bi-check2-square me-1"></i> Mark as Submitted</h6>
                            <p>Once you've copied a proposal and submitted it on the freelance platform, mark it as <strong>"Submitted"</strong> in the system. This helps you track which jobs you've applied to and measure your success rate.</p>

                            <h6><i class="bi bi-chat-dots me-1"></i> Client Feedback</h6>
                            <p>After submitting, you can record feedback or outcomes (interview received, rejected, hired) to refine your approach over time.</p>

                            <h6><i class="bi bi-file-pdf me-1"></i> PDF Export</h6>
                            <p>Need a formatted version? Use the <strong>"Download PDF"</strong> option to get a clean, professional PDF of your proposal.</p>
                        </div>
                    </div>
                </div>

                <!-- Platform Rules -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingRules">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRules" aria-expanded="false" aria-controls="collapseRules">
                            <i class="bi bi-list-check me-2 text-secondary"></i>
                            <strong>Platform Rules</strong>
                        </button>
                    </h2>
                    <div id="collapseRules" class="accordion-collapse collapse" aria-labelledby="headingRules" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <h6><i class="bi bi-sliders me-1"></i> Custom Rules per Platform</h6>
                            <p>Different freelance platforms have different expectations. The <strong>Rules</strong> section lets you define platform-specific instructions that the AI follows when generating proposals.</p>

                            <h6><i class="bi bi-chat-quote me-1"></i> Tone &amp; Style</h6>
                            <p>Set rules for tone (formal vs. casual), length constraints, opening hooks, and closing statements. For example:</p>
                            <ul>
                                <li>"Keep Upwork proposals under 300 words"</li>
                                <li>"Use a conversational tone for Fiverr responses"</li>
                                <li>"Always open with a question about the client's project"</li>
                                <li>"Include a brief case study reference when possible"</li>
                            </ul>

                            <h6><i class="bi bi-file-earmark-text me-1"></i> Templates</h6>
                            <p>Rules can function as lightweight templates &mdash; defining structural elements that every proposal should include, such as an introduction, relevant experience section, proposed approach, and timeline.</p>

                            <h6><i class="bi bi-toggle-on me-1"></i> Enable / Disable</h6>
                            <p>Toggle rules on or off without deleting them. This lets you experiment with different approaches and quickly switch between styles.</p>

                            <h6><i class="bi bi-arrow-down-up me-1"></i> Priority Ordering</h6>
                            <p>Drag and reorder rules to set priority. Higher-priority rules take precedence when the AI needs to make trade-offs (e.g., length vs. detail).</p>
                        </div>
                    </div>
                </div>

                <!-- Calendar & Availability -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingCalendar">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCalendar" aria-expanded="false" aria-controls="collapseCalendar">
                            <i class="bi bi-calendar3 me-2 text-success"></i>
                            <strong>Calendar &amp; Availability</strong>
                        </button>
                    </h2>
                    <div id="collapseCalendar" class="accordion-collapse collapse" aria-labelledby="headingCalendar" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <h6><i class="bi bi-calendar-check me-1"></i> Schedule Management</h6>
                            <p>The <strong>Calendar</strong> section lets you manage your availability. Add blocks of time when you're available for new work, or mark periods when you're booked.</p>

                            <h6><i class="bi bi-clock me-1"></i> How It Helps</h6>
                            <p>Your availability information can be referenced in proposals to give clients confidence about your capacity. The AI can mention your start date or weekly hours based on your calendar entries.</p>

                            <h6><i class="bi bi-calendar-plus me-1"></i> Adding Entries</h6>
                            <p>Click on a date to add an availability entry. Specify the type (available, busy, tentative), time range, and any notes. You can edit or delete entries as your schedule changes.</p>
                        </div>
                    </div>
                </div>

                <!-- Tips for Better Proposals -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTips">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTips" aria-expanded="false" aria-controls="collapseTips">
                            <i class="bi bi-lightbulb me-2 text-warning"></i>
                            <strong>Tips for Better Proposals</strong>
                        </button>
                    </h2>
                    <div id="collapseTips" class="accordion-collapse collapse" aria-labelledby="headingTips" data-bs-parent="#guideAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-pencil-fill text-primary me-1"></i> Write Detailed Job Descriptions</h6>
                                            <p class="card-text small">When adding a job, include the full posting text. The more context the AI has, the more personalized and relevant your proposal will be.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-arrow-clockwise text-success me-1"></i> Keep Your Resume Updated</h6>
                                            <p class="card-text small">Add new projects, skills, and certifications as you complete them. A current resume produces stronger, more accurate proposals.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-graph-up-arrow text-info me-1"></i> Use the Fit Score</h6>
                                            <p class="card-text small">Focus your energy on jobs with a fit score of 70% or higher. You'll get better response rates and waste less time on long-shot applications.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-sliders text-danger me-1"></i> Fine-Tune Your Rules</h6>
                                            <p class="card-text small">Experiment with different platform rules and tones. Small changes in instructions can significantly improve proposal quality and client engagement.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-person-check text-warning me-1"></i> Always Personalize</h6>
                                            <p class="card-text small">Edit every AI-generated proposal before sending. Add a personal touch, reference the client's specific project, or mention their company by name.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><i class="bi bi-clipboard-data text-secondary me-1"></i> Track Your Results</h6>
                                            <p class="card-text small">Mark proposals as submitted and record outcomes. Over time, you'll see which approaches work best and can refine your strategy accordingly.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end accordion -->

            <div class="card mt-4 border-0 bg-light">
                <div class="card-body text-center py-4">
                    <i class="bi bi-envelope-at fs-3 text-muted"></i>
                    <p class="mb-1 mt-2">Need more help or have a feature request?</p>
                    <p class="text-muted small mb-0">Contact your administrator or check back here for updates as new features are added.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>

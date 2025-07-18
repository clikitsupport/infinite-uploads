<?php
namespace ClikIT\Infinite_Uploads\Aws\AuditManager;

use ClikIT\Infinite_Uploads\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Audit Manager** service.
 * @method \ClikIT\Infinite_Uploads\Aws\Result associateAssessmentReportEvidenceFolder(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise associateAssessmentReportEvidenceFolderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchAssociateAssessmentReportEvidence(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchAssociateAssessmentReportEvidenceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchCreateDelegationByAssessment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchCreateDelegationByAssessmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchDeleteDelegationByAssessment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchDeleteDelegationByAssessmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchDisassociateAssessmentReportEvidence(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchDisassociateAssessmentReportEvidenceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result batchImportEvidenceToAssessmentControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise batchImportEvidenceToAssessmentControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAssessment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAssessmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAssessmentFramework(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAssessmentFrameworkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createAssessmentReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createAssessmentReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result createControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise createControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAssessment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAssessmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAssessmentFramework(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAssessmentFrameworkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAssessmentFrameworkShare(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAssessmentFrameworkShareAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteAssessmentReport(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteAssessmentReportAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deleteControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deleteControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterAccount(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterAccountAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result deregisterOrganizationAdminAccount(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise deregisterOrganizationAdminAccountAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result disassociateAssessmentReportEvidenceFolder(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise disassociateAssessmentReportEvidenceFolderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAccountStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAccountStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAssessment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAssessmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAssessmentFramework(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAssessmentFrameworkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getAssessmentReportUrl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getAssessmentReportUrlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getChangeLogs(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getChangeLogsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getDelegations(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getDelegationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEvidence(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEvidenceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEvidenceByEvidenceFolder(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEvidenceByEvidenceFolderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEvidenceFileUploadUrl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEvidenceFileUploadUrlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEvidenceFolder(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEvidenceFolderAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEvidenceFoldersByAssessment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEvidenceFoldersByAssessmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getEvidenceFoldersByAssessmentControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getEvidenceFoldersByAssessmentControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getInsights(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getInsightsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getInsightsByAssessment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getInsightsByAssessmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getOrganizationAdminAccount(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getOrganizationAdminAccountAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getServicesInScope(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getServicesInScopeAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result getSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise getSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAssessmentControlInsightsByControlDomain(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAssessmentControlInsightsByControlDomainAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAssessmentFrameworkShareRequests(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAssessmentFrameworkShareRequestsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAssessmentFrameworks(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAssessmentFrameworksAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAssessmentReports(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAssessmentReportsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listAssessments(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listAssessmentsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listControlDomainInsights(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listControlDomainInsightsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listControlDomainInsightsByAssessment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listControlDomainInsightsByAssessmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listControlInsightsByControlDomain(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listControlInsightsByControlDomainAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listControls(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listControlsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listKeywordsForDataSource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listKeywordsForDataSourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listNotifications(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listNotificationsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result listTagsForResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerAccount(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerAccountAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result registerOrganizationAdminAccount(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise registerOrganizationAdminAccountAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result startAssessmentFrameworkShare(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise startAssessmentFrameworkShareAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result tagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result untagResource(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAssessment(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAssessmentAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAssessmentControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAssessmentControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAssessmentControlSetStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAssessmentControlSetStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAssessmentFramework(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAssessmentFrameworkAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAssessmentFrameworkShare(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAssessmentFrameworkShareAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateAssessmentStatus(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateAssessmentStatusAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateControl(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateControlAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result updateSettings(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise updateSettingsAsync(array $args = [])
 * @method \ClikIT\Infinite_Uploads\Aws\Result validateAssessmentReportIntegrity(array $args = [])
 * @method \ClikIT\Infinite_Uploads\GuzzleHttp\Promise\Promise validateAssessmentReportIntegrityAsync(array $args = [])
 */
class AuditManagerClient extends AwsClient {}

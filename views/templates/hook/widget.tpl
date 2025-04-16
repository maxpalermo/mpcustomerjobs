{* Widget for displaying customer job info *}
{if $customer_job}
    <div class="customer-job-widget">
        <strong>{$hookName|escape:'html':'UTF-8'}:</strong><br>
        Settore: {$customer_job.id_customer_job_area|escape:'html':'UTF-8'}<br>
        Professione: {$customer_job.id_customer_job_name|escape:'html':'UTF-8'}
    </div>
{else}
    <div class="customer-job-widget-empty">
        Nessuna professione associata.
    </div>
{/if}

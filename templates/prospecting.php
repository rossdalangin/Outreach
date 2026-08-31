<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap cc-outreach-wrap">
    <h1><span class="dashicons dashicons-search"></span> Find Leads (AI Internet Prospecting)</h1>
    <p>Search the internet for coaches, consultants, and service providers in your target industry and automatically import them into your CloseClient database.</p>

    <div class="cc-card">
        <h2>Prospecting Search Parameters</h2>
        <form id="cc-form-find-leads">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="prospect_industry">Target Industry / Niche</label></th>
                    <td>
                        <select id="prospect_industry" name="industry" class="regular-text">
                            <option value="Business Coach">Business Coach</option>
                            <option value="Life Coach">Life Coach</option>
                            <option value="Executive Coach">Executive Coach</option>
                            <option value="Business Consultant">Business Consultant</option>
                            <option value="Marketing Consultant">Marketing Consultant</option>
                            <option value="Small Agency">Small Agency Owner</option>
                            <option value="Financial Consultant">Financial Consultant</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prospect_location">Target Location</label></th>
                    <td>
                        <input type="text" id="prospect_location" name="location" value="United States" class="regular-text" placeholder="e.g. United States, California, London UK" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prospect_quantity">Number of Prospects</label></th>
                    <td>
                        <input type="number" id="prospect_quantity" name="quantity" value="5" min="1" max="10" class="small-text" />
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" id="cc-btn-run-prospecting" class="button button-primary button-large"><span class="dashicons dashicons-search"></span> Search Internet & Import Leads</button>
            </p>
        </form>
    </div>

    <div id="cc-prospecting-results" class="cc-card" style="display:none;">
        <h2>Discovery Results</h2>
        <div id="cc-prospecting-output"></div>
    </div>
</div>

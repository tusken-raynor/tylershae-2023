<?php
if (tylershae_support_example($block)) return;
$block_id = get_field('block_id') ?: 'contact-us-' . $block['id'];
$action_url = get_field('action_url');
$title = get_field('title');
$button_label = get_field('button_label') ?: 'Submit';
$nonce = wp_create_nonce("ffh_nonce");
block_styles($block_id);
?>
<section 
  id="<?= $block_id; ?>" 
  class="tylershae-block contact-us-block container" 
>
  <h2><?= $title ?></h2>
  <form
    enctype="multipart/form-data"
    method="post"
    action="<?= $action_url ?>"
    class="frm-show-form"
  >
    <div class="frm_form_fields">
      <fieldset>
        <legend class="frm_screen_reader">Contact Us</legend>
        <div class="frm_fields_container">
          <input type="hidden" name="ffh_nonce" value="<?= $nonce ?>">
          <input type="hidden" name="redirect" value="<?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?>#<?= $block_id ?>">
          <div
            class="frm_form_field form-field frm_half"
          >
            <label
              for="field_first-name"
              class="frm_primary_label"
            >First Name</label>
            <input
              type="text"
              id="field_first-name"
              name="first-name"
              value=""
              aria-required="true"
              required
            />
          </div>
          <div
            class="frm_form_field form-field frm_half"
          >
            <label
              for="field_last-name"
              class="frm_primary_label"
            >Last Name</label>
            <input
              type="text"
              id="field_last-name"
              name="last-name"
              value=""
              aria-required="true"
              required
            />
          </div>
          <div
            class="frm_form_field form-field frm_full"
          >
            <label
              for="field_email"
              class="frm_primary_label"
            >Email</label>
            <input
              type="email"
              id="field_email"
              name="email"
              value=""
              aria-required="true"
              required
            />
          </div>
          <div
            class="frm_form_field form-field frm_full"
          >
            <label
              for="field_subject"
              class="frm_primary_label"
            >Subject</label>
            <input
              type="text"
              id="field_subject"
              name="subject"
              value=""
              aria-required="true"
              required
            />
          </div>
          <div
            class="frm_form_field form-field frm_full"
          >
            <label
              for="field_message"
              class="frm_primary_label"
            >Message</label>
            <textarea
              name="message"
              id="field_message"
              rows="5"
              aria-required="true"
              required
            ></textarea>
          </div>
          <div class="frm_verify" aria-hidden="true">
            <label for="frm_email_1">
              If thou be human, thou shalt not fill this field.
            </label>
            <input
              type="text"
              class="frm_verify"
              id="frm_email_1"
              name="frm_verify<?= $nonce ?>"
              value=""
            />
          </div>
          <div class="frm_verify" aria-hidden="true">
            <label for="frm_email_2">
              If thou be human, thou shalt not fill this field.
            </label>
            <input
              type="text"
              class="rse-validate"
              id="frm_email_2"
              name="rse-validate_<?= get_rse_id() ?>"
              value=""
            />
          </div>
          <div class="frm_submit">
            <button 
              class="frm_button_submit" 
              type="submit"
            ><?= $button_label ?></button>
          </div>
        </div>
      </fieldset>
    </div>
  </form>
</section>
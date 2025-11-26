/**
 * announcements.js
 * Handles slug, media picker, repeatable speakers/organizers, datetime validation
 *
 * Requires: jQuery, wp.media (WordPress admin)
 */
(function ($) {
  'use strict';

  const WPSG = window.WPSG_ANN_DATA || {}; // localized by PHP if available
  const ajaxurl = WPSG.ajax_url || window.ajaxurl || '/wp-admin/admin-ajax.php';
  const postId = WPSG.post_id || 0;
  const slugCheckAction = WPSG.slug_check_action || 'wpsg_check_slug';

  const Ann = {
    init() {
      this.cache();
      this.bindAll();
      this.loadExisting();
    },

    cache() {
      this.$title = $('#ann_title');
      this.$slug = $('#ann_slug');
      this.$slugStatus = $('#slug-status');
      this.$imageField = $('#ann_image');
      this.$imageWrapper = $('#ann_image_wrapper');
      this.$imageSelectBtn = $('#ann_image_select');
      this.$imageRemoveBtn = $('#ann_image_remove');
      this.$speakersWrap = $('#speakers_wrapper');
      this.$organizersWrap = $('#organizers_wrapper');
      this.$addSpeakerBtn = $('#add_speaker');
      this.$addOrganizerBtn = $('#add_organizer');
      this.$dateStart = $('#date_start');
      this.$dateEnd = $('#date_end');
      this.$timeStart = $('#time_start');
      this.$timeEnd = $('#time_end');

      // place for validation messages
      this.$datetimeError = $('<div id="wpsg-datetime-error" style="color:#a00;margin-top:8px"></div>');
      $('.wpsg-datetime-row').last().after(this.$datetimeError);
    },

    bindAll() {
      // Slug behavior
      this.bindSlug();

      // Image picker
      this.bindImagePicker();

      // Repeaters
      this.$addSpeakerBtn.on('click', () => this.addSpeaker());
      this.$addOrganizerBtn.on('click', () => this.addOrganizer());
      // delegate remove
      this.$speakersWrap.on('click', '.wpsg-remove-item', (e) => {
        $(e.currentTarget).closest('.repeatable-item').remove();
      });
      this.$organizersWrap.on('click', '.wpsg-remove-item', (e) => {
        $(e.currentTarget).closest('.repeatable-item').remove();
      });

      // Date/time validation
      this.$dateStart.add(this.$dateEnd).add(this.$timeStart).add(this.$timeEnd).on('change', () => this.validateDatetime());
    },

    // --------------------
    // Slug
    // --------------------
    bindSlug() {
      const self = this;
      // set initial mode attribute
      this.$slug.data('mode', this.$slug.val() ? 'manual' : 'auto');

      // generate slug from title if in auto mode
      this.$title.on('input', function () {
        if (self.$slug.data('mode') === 'auto') {
          const s = Ann.slugify($(this).val());
          self.$slug.val(s);
          // optional: check uniqueness delayed
          self.debounce(() => self.checkSlug(s), 600);
        }
      });

      // when user types in slug -> manual mode and sanitize
      this.$slug.on('input', function () {
        const raw = $(this).val();
        const s = Ann.slugify(raw);
        $(this).val(s);
        $(this).data('mode', 'manual');
        // check uniqueness
        self.debounce(() => self.checkSlug(s), 500);
      });

      // when slug blurred and empty -> go back to auto
      this.$slug.on('blur', function () {
        if (!$(this).val()) {
          $(this).data('mode', 'auto');
          $(this).val(Ann.slugify(self.$title.val()));
          self.checkSlug($(this).val());
        }
      });

      // initial check if slug exists
      if (this.$slug.val()) {
        this.checkSlug(this.$slug.val());
      }
    },

    slugify(text) {
      if (!text) return '';
      return text.toString()
        .normalize('NFD')                   // decompose diacritics
        .replace(/[\u0300-\u036f]/g, '')    // remove diacritics
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')        // replace non-alphanum with -
        .replace(/-+/g, '-')                // collapse multiple -
        .replace(/^-|-$/g, '');             // trim leading/trailing -
    },

    debounce(fn, wait) {
      clearTimeout(this._debounceTimer);
      this._debounceTimer = setTimeout(fn, wait || 300);
    },

    checkSlug(slug) {
      const self = this;
      if (!slug) {
        self.$slugStatus.text('');
        return;
      }

      // show busy indicator
      self.$slugStatus.text('Checking…');

      $.post(ajaxurl, {
        action: slugCheckAction,
        slug: slug,
        post_id: postId,
        _ajax_nonce: WPSG.nonce || ''
      }).done(function (res) {
        // Expected response: { ok: true/false, message: "...", slug: "..." }
        try {
          const data = (typeof res === 'object') ? res : JSON.parse(res);
          if (data && data.ok) {
            self.$slugStatus.css('color', '#0a0').text(data.message || 'Available');
          } else {
            self.$slugStatus.css('color', '#a00').text(data.message || 'Already in use');
          }
        } catch (err) {
          // fallback
          if (res && res.success) {
            self.$slugStatus.css('color', '#0a0').text('Available');
          } else {
            self.$slugStatus.css('color', '#a00').text('Already in use');
          }
        }
      }).fail(function () {
        self.$slugStatus.css('color', '#a00').text('Error checking slug');
      });
    },

    // --------------------
    // Media (Featured Image)
    // --------------------
    bindImagePicker() {
      let frame;
      const self = this;

      self.$imageSelectBtn.on('click', function (e) {
        e.preventDefault();

        // If frame exists, reopen
        if (frame) {
          frame.open();
          return;
        }

        // Create new media frame
        frame = wp.media({
          title: 'Select Featured Image',
          button: { text: 'Use this image' },
          multiple: false
        });

        frame.on('select', function () {
          const attachment = frame.state().get('selection').first().toJSON();
          if (attachment && attachment.url) {
            self.$imageField.val(attachment.url);
            // update preview (remove old first)
            self.$imageWrapper.find('img.preview-image').remove();
            const $img = $('<img class="preview-image">').attr('src', attachment.url);
            self.$imageWrapper.prepend($img);
            self.$imageRemoveBtn.show();
          }
        });

        frame.open();
      });

      // Remove image
      self.$imageRemoveBtn.on('click', function (e) {
        e.preventDefault();
        self.$imageField.val('');
        self.$imageWrapper.find('img.preview-image').remove();
        $(this).hide();
      });

      // hide remove button when no image
      if (!self.$imageField.val()) {
        self.$imageRemoveBtn.hide();
      }
    },

    // --------------------
    // Repeatable: Speakers
    // --------------------
    speakerTemplate(item = {}) {
      const index = Date.now() + Math.floor(Math.random() * 999);
      const name = item.name ? this.escapeHtml(item.name) : '';
      const org = item.organization ? this.escapeHtml(item.organization) : '';
      const pos = item.position ? this.escapeHtml(item.position) : '';

      return `
        <div class="repeatable-item">
          <div class="repeatable-item-fields">
            <input type="text" name="speakers[][name]" placeholder="Full name" value="${name}" />
            <input type="text" name="speakers[][organization]" placeholder="Organization" value="${org}" />
            <input type="text" name="speakers[][position]" placeholder="Position" value="${pos}" />
          </div>
          <div>
            <button type="button" class="button wpsg-remove-item">Remove</button>
          </div>
        </div>
      `;
    },

    addSpeaker(item = {}) {
      const html = this.speakerTemplate(item);
      this.$speakersWrap.append(html);
    },

    // --------------------
    // Repeatable: Organizers
    // --------------------
    organizerTemplate(item = {}) {
      const name = item.name ? this.escapeHtml(item.name) : '';
      const status = item.status ? this.escapeHtml(item.status) : '';
      const contact = item.contact ? this.escapeHtml(item.contact) : '';

      return `
        <div class="repeatable-item">
          <div class="repeatable-item-fields">
            <input type="text" name="organizers[][name]" placeholder="Organizer name" value="${name}" />
            <input type="text" name="organizers[][status]" placeholder="Status (e.g. main sponsor)" value="${status}" />
            <input type="text" name="organizers[][contact]" placeholder="Contact (optional)" value="${contact}" />
          </div>
          <div>
            <button type="button" class="button wpsg-remove-item">Remove</button>
          </div>
        </div>
      `;
    },

    addOrganizer(item = {}) {
      const html = this.organizerTemplate(item);
      this.$organizersWrap.append(html);
    },

    // --------------------
    // Datetime validation
    // --------------------
    validateDatetime() {
      const sDate = this.$dateStart.val();
      const eDate = this.$dateEnd.val();
      const sTime = this.$timeStart.val();
      const eTime = this.$timeEnd.val();

      // clear
      this.$datetimeError.text('');

      if (!sDate || !eDate) return true;

      const start = new Date(sDate + ' ' + (sTime || '00:00'));
      const end = new Date(eDate + ' ' + (eTime || '23:59'));

      if (end < start) {
        this.$datetimeError.text('End date/time must be after start date/time.');
        return false;
      }

      return true;
    },

    // --------------------
    // Load existing data into repeaters
    // --------------------
    loadExisting() {
      // Prefer localized object first
      const data = window.WPSG_ANN_DATA && window.WPSG_ANN_DATA.existing ? window.WPSG_ANN_DATA.existing : null;

      // Or fallback to hidden json element with id #wpsg_ann_json
      if (!data) {
        const $hidden = $('#wpsg_ann_json');
        if ($hidden.length) {
          try {
            const parsed = JSON.parse($hidden.val());
            if (parsed && parsed.existing) {
              this.populateFrom(parsed.existing);
              return;
            }
          } catch (e) { /* ignore parse error */ }
        }
      }

      if (data) {
        this.populateFrom(data);
      }
    },

    populateFrom(existing) {
      // speakers
      if (existing.speakers && Array.isArray(existing.speakers)) {
        existing.speakers.forEach(s => this.addSpeaker(s));
      }

      // organizers
      if (existing.organizers && Array.isArray(existing.organizers)) {
        existing.organizers.forEach(o => this.addOrganizer(o));
      }

      // featured image preview if present
      if (existing.image) {
        this.$imageWrapper.find('img.preview-image').remove();
        const $img = $('<img class="preview-image">').attr('src', existing.image);
        this.$imageWrapper.prepend($img);
        this.$imageField.val(existing.image);
        this.$imageRemoveBtn.show();
      }
    },

    escapeHtml(text) {
      return String(text)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    }
  }; // Ann

  // Initialize on DOM ready
  $(function () {
    if (typeof wp === 'undefined' || typeof jQuery === 'undefined') {
      // still initialize core functions
      Ann.init();
    } else {
      // ensure media scripts available
      if (!wp.media) {
        // load media scripts (if not already)
        // In WP admin usually wp_enqueue_media() is called from PHP
      }
      Ann.init();
    }
  });

})(jQuery);

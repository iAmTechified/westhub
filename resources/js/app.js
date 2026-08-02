import './bootstrap';

window.galleryLightbox = function (images = []) {
    const normalizedImages = Array.isArray(images)
        ? images
            .map((image) => ({
                url: image?.url || '',
                alt: image?.alt || image?.title || 'Gallery image',
            }))
            .filter((image) => image.url)
        : [];

    return {
        images: normalizedImages,
        lightboxOpen: false,
        currentIndex: 0,
        slideDirection: 'forward',
        slideOffset: 0,
        previousBodyOverflow: null,

        init() {
            this.initGalleryLightbox();
        },

        initGalleryLightbox() {
            window.addEventListener('resize', () => this.refreshLightboxLayout());
        },

        openLightbox(index) {
            const targetIndex = Number(index);

            if (!Number.isInteger(targetIndex) || !this.images[targetIndex]) {
                return;
            }

            this.currentIndex = targetIndex;
            this.slideDirection = 'forward';
            this.lightboxOpen = true;
            this.lockBodyScroll();
            this.slideOffset = this.slideOffset || this.estimateSlideOffset();
            this.refreshLightboxLayout();
        },

        closeLightbox() {
            if (!this.lightboxOpen) {
                return;
            }

            this.lightboxOpen = false;
            document.body.style.overflow = this.previousBodyOverflow ?? '';
            this.previousBodyOverflow = null;
        },

        currentImage() {
            return this.images[this.currentIndex] || { url: '', alt: 'Gallery image' };
        },

        hasPrevious() {
            return this.lightboxOpen && this.currentIndex > 0;
        },

        hasNext() {
            return this.lightboxOpen && this.currentIndex < this.images.length - 1;
        },

        previousImage() {
            if (!this.hasPrevious()) {
                return;
            }

            this.slideDirection = 'backward';
            this.currentIndex -= 1;
            this.refreshLightboxLayout();
        },

        nextImage() {
            if (!this.hasNext()) {
                return;
            }

            this.slideDirection = 'forward';
            this.currentIndex += 1;
            this.refreshLightboxLayout();
        },

        trackStyle() {
            const offset = this.slideOffset || this.estimateSlideOffset();

            return `transform: translate3d(${-this.currentIndex * offset}px, 0, 0);`;
        },

        trackClass() {
            return this.slideDirection === 'backward'
                ? 'is-bouncing-backward'
                : 'is-bouncing-forward';
        },

        lockBodyScroll() {
            if (this.previousBodyOverflow === null) {
                this.previousBodyOverflow = document.body.style.overflow;
            }

            document.body.style.overflow = 'hidden';
        },

        refreshLightboxLayout() {
            if (!this.lightboxOpen) {
                return;
            }

            this.$nextTick(() => {
                window.requestAnimationFrame(() => this.updateSlideMetrics());
            });
        },

        updateSlideMetrics() {
            const track = this.$refs.lightboxTrack;
            const slide = track?.querySelector('[data-lightbox-slide]');

            if (!track || !slide) {
                this.slideOffset = this.estimateSlideOffset();
                return;
            }

            const styles = window.getComputedStyle(track);
            const gap = Number.parseFloat(styles.columnGap || styles.gap || '0') || 0;
            this.slideOffset = slide.getBoundingClientRect().width + gap;
        },

        estimateSlideOffset() {
            const viewportWidth = window.innerWidth || 1024;
            const slideWidth = viewportWidth < 768
                ? viewportWidth * 0.92
                : Math.min(viewportWidth * 0.88, 1068);
            const gap = Math.min(Math.max(viewportWidth * 0.016, 8), 18);

            return slideWidth + gap;
        },
    };
};

window.homeGallery = function (images = []) {
    return {
        ...window.galleryLightbox(images),
        autoScrollInterval: null,
        hoverPaused: false,

        init() {
            this.initGalleryLightbox();
            this.setupScroll();
            window.addEventListener('resize', () => this.setupScroll());
        },

        setupScroll() {
            const container = this.$refs.slider;

            if (!container) {
                return;
            }

            this.stopScroll();

            if (container.scrollWidth <= container.clientWidth) {
                return;
            }

            this.autoScrollInterval = window.setInterval(() => {
                if (this.hoverPaused || this.lightboxOpen) {
                    return;
                }

                const resetPoint = container.scrollWidth / 2;

                if (container.scrollLeft >= resetPoint) {
                    container.scrollLeft = 0;
                    return;
                }

                container.scrollLeft += 1;
            }, 32);
        },

        stopScroll() {
            if (!this.autoScrollInterval) {
                return;
            }

            window.clearInterval(this.autoScrollInterval);
            this.autoScrollInterval = null;
        },

        pauseScroll() {
            this.hoverPaused = true;
        },

        resumeScroll() {
            this.hoverPaused = false;
        },
    };
};

const joinTitleOptions = [
    { label: 'Mr.', value: 'Mr.' },
    { label: 'Mrs.', value: 'Mrs.' },
    { label: 'Ms.', value: 'Ms.' },
    { label: 'Miss', value: 'Miss' },
    { label: 'Dr.', value: 'Dr.' },
    { label: 'Prof.', value: 'Prof.' },
    { label: 'Rev.', value: 'Rev.' },
];

const joinCountryOptions = [
    { flag: '🇦🇫', label: 'Afghanistan', value: '+93', iso: 'AF' },
    { flag: '🇦🇱', label: 'Albania', value: '+355', iso: 'AL' },
    { flag: '🇩🇿', label: 'Algeria', value: '+213', iso: 'DZ' },
    { flag: '🇦🇸', label: 'American Samoa', value: '+1', iso: 'AS' },
    { flag: '🇦🇩', label: 'Andorra', value: '+376', iso: 'AD' },
    { flag: '🇦🇴', label: 'Angola', value: '+244', iso: 'AO' },
    { flag: '🇦🇮', label: 'Anguilla', value: '+1', iso: 'AI' },
    { flag: '🇦🇬', label: 'Antigua and Barbuda', value: '+1', iso: 'AG' },
    { flag: '🇦🇷', label: 'Argentina', value: '+54', iso: 'AR' },
    { flag: '🇦🇲', label: 'Armenia', value: '+374', iso: 'AM' },
    { flag: '🇦🇼', label: 'Aruba', value: '+297', iso: 'AW' },
    { flag: '🇦🇺', label: 'Australia', value: '+61', iso: 'AU' },
    { flag: '🇦🇹', label: 'Austria', value: '+43', iso: 'AT' },
    { flag: '🇦🇿', label: 'Azerbaijan', value: '+994', iso: 'AZ' },
    { flag: '🇧🇸', label: 'Bahamas', value: '+1', iso: 'BS' },
    { flag: '🇧🇭', label: 'Bahrain', value: '+973', iso: 'BH' },
    { flag: '🇧🇩', label: 'Bangladesh', value: '+880', iso: 'BD' },
    { flag: '🇧🇧', label: 'Barbados', value: '+1', iso: 'BB' },
    { flag: '🇧🇾', label: 'Belarus', value: '+375', iso: 'BY' },
    { flag: '🇧🇪', label: 'Belgium', value: '+32', iso: 'BE' },
    { flag: '🇧🇿', label: 'Belize', value: '+501', iso: 'BZ' },
    { flag: '🇧🇯', label: 'Benin', value: '+229', iso: 'BJ' },
    { flag: '🇧🇲', label: 'Bermuda', value: '+1', iso: 'BM' },
    { flag: '🇧🇹', label: 'Bhutan', value: '+975', iso: 'BT' },
    { flag: '🇧🇴', label: 'Bolivia', value: '+591', iso: 'BO' },
    { flag: '🇧🇦', label: 'Bosnia and Herzegovina', value: '+387', iso: 'BA' },
    { flag: '🇧🇼', label: 'Botswana', value: '+267', iso: 'BW' },
    { flag: '🇧🇷', label: 'Brazil', value: '+55', iso: 'BR' },
    { flag: '🇻🇬', label: 'British Virgin Islands', value: '+1', iso: 'VG' },
    { flag: '🇧🇳', label: 'Brunei', value: '+673', iso: 'BN' },
    { flag: '🇧🇬', label: 'Bulgaria', value: '+359', iso: 'BG' },
    { flag: '🇧🇫', label: 'Burkina Faso', value: '+226', iso: 'BF' },
    { flag: '🇧🇮', label: 'Burundi', value: '+257', iso: 'BI' },
    { flag: '🇰🇭', label: 'Cambodia', value: '+855', iso: 'KH' },
    { flag: '🇨🇲', label: 'Cameroon', value: '+237', iso: 'CM' },
    { flag: '🇨🇦', label: 'Canada', value: '+1', iso: 'CA' },
    { flag: '🇨🇻', label: 'Cape Verde', value: '+238', iso: 'CV' },
    { flag: '🇰🇾', label: 'Cayman Islands', value: '+1', iso: 'KY' },
    { flag: '🇨🇫', label: 'Central African Republic', value: '+236', iso: 'CF' },
    { flag: '🇹🇩', label: 'Chad', value: '+235', iso: 'TD' },
    { flag: '🇨🇱', label: 'Chile', value: '+56', iso: 'CL' },
    { flag: '🇨🇳', label: 'China', value: '+86', iso: 'CN' },
    { flag: '🇨🇴', label: 'Colombia', value: '+57', iso: 'CO' },
    { flag: '🇰🇲', label: 'Comoros', value: '+269', iso: 'KM' },
    { flag: '🇨🇬', label: 'Congo', value: '+242', iso: 'CG' },
    { flag: '🇨🇩', label: 'Congo, Democratic Republic', value: '+243', iso: 'CD' },
    { flag: '🇨🇰', label: 'Cook Islands', value: '+682', iso: 'CK' },
    { flag: '🇨🇷', label: 'Costa Rica', value: '+506', iso: 'CR' },
    { flag: '🇨🇮', label: "Cote d'Ivoire", value: '+225', iso: 'CI' },
    { flag: '🇭🇷', label: 'Croatia', value: '+385', iso: 'HR' },
    { flag: '🇨🇺', label: 'Cuba', value: '+53', iso: 'CU' },
    { flag: '🇨🇼', label: 'Curacao', value: '+599', iso: 'CW' },
    { flag: '🇨🇾', label: 'Cyprus', value: '+357', iso: 'CY' },
    { flag: '🇨🇿', label: 'Czech Republic', value: '+420', iso: 'CZ' },
    { flag: '🇩🇰', label: 'Denmark', value: '+45', iso: 'DK' },
    { flag: '🇩🇯', label: 'Djibouti', value: '+253', iso: 'DJ' },
    { flag: '🇩🇲', label: 'Dominica', value: '+1', iso: 'DM' },
    { flag: '🇩🇴', label: 'Dominican Republic', value: '+1', iso: 'DO' },
    { flag: '🇪🇨', label: 'Ecuador', value: '+593', iso: 'EC' },
    { flag: '🇪🇬', label: 'Egypt', value: '+20', iso: 'EG' },
    { flag: '🇸🇻', label: 'El Salvador', value: '+503', iso: 'SV' },
    { flag: '🇬🇶', label: 'Equatorial Guinea', value: '+240', iso: 'GQ' },
    { flag: '🇪🇷', label: 'Eritrea', value: '+291', iso: 'ER' },
    { flag: '🇪🇪', label: 'Estonia', value: '+372', iso: 'EE' },
    { flag: '🇸🇿', label: 'Eswatini', value: '+268', iso: 'SZ' },
    { flag: '🇪🇹', label: 'Ethiopia', value: '+251', iso: 'ET' },
    { flag: '🇫🇰', label: 'Falkland Islands', value: '+500', iso: 'FK' },
    { flag: '🇫🇴', label: 'Faroe Islands', value: '+298', iso: 'FO' },
    { flag: '🇫🇯', label: 'Fiji', value: '+679', iso: 'FJ' },
    { flag: '🇫🇮', label: 'Finland', value: '+358', iso: 'FI' },
    { flag: '🇫🇷', label: 'France', value: '+33', iso: 'FR' },
    { flag: '🇬🇫', label: 'French Guiana', value: '+594', iso: 'GF' },
    { flag: '🇵🇫', label: 'French Polynesia', value: '+689', iso: 'PF' },
    { flag: '🇬🇦', label: 'Gabon', value: '+241', iso: 'GA' },
    { flag: '🇬🇲', label: 'Gambia', value: '+220', iso: 'GM' },
    { flag: '🇬🇪', label: 'Georgia', value: '+995', iso: 'GE' },
    { flag: '🇩🇪', label: 'Germany', value: '+49', iso: 'DE' },
    { flag: '🇬🇭', label: 'Ghana', value: '+233', iso: 'GH' },
    { flag: '🇬🇮', label: 'Gibraltar', value: '+350', iso: 'GI' },
    { flag: '🇬🇷', label: 'Greece', value: '+30', iso: 'GR' },
    { flag: '🇬🇱', label: 'Greenland', value: '+299', iso: 'GL' },
    { flag: '🇬🇩', label: 'Grenada', value: '+1', iso: 'GD' },
    { flag: '🇬🇵', label: 'Guadeloupe', value: '+590', iso: 'GP' },
    { flag: '🇬🇺', label: 'Guam', value: '+1', iso: 'GU' },
    { flag: '🇬🇹', label: 'Guatemala', value: '+502', iso: 'GT' },
    { flag: '🇬🇬', label: 'Guernsey', value: '+44', iso: 'GG' },
    { flag: '🇬🇳', label: 'Guinea', value: '+224', iso: 'GN' },
    { flag: '🇬🇼', label: 'Guinea-Bissau', value: '+245', iso: 'GW' },
    { flag: '🇬🇾', label: 'Guyana', value: '+592', iso: 'GY' },
    { flag: '🇭🇹', label: 'Haiti', value: '+509', iso: 'HT' },
    { flag: '🇭🇳', label: 'Honduras', value: '+504', iso: 'HN' },
    { flag: '🇭🇰', label: 'Hong Kong', value: '+852', iso: 'HK' },
    { flag: '🇭🇺', label: 'Hungary', value: '+36', iso: 'HU' },
    { flag: '🇮🇸', label: 'Iceland', value: '+354', iso: 'IS' },
    { flag: '🇮🇳', label: 'India', value: '+91', iso: 'IN' },
    { flag: '🇮🇩', label: 'Indonesia', value: '+62', iso: 'ID' },
    { flag: '🇮🇷', label: 'Iran', value: '+98', iso: 'IR' },
    { flag: '🇮🇶', label: 'Iraq', value: '+964', iso: 'IQ' },
    { flag: '🇮🇪', label: 'Ireland', value: '+353', iso: 'IE' },
    { flag: '🇮🇲', label: 'Isle of Man', value: '+44', iso: 'IM' },
    { flag: '🇮🇱', label: 'Israel', value: '+972', iso: 'IL' },
    { flag: '🇮🇹', label: 'Italy', value: '+39', iso: 'IT' },
    { flag: '🇯🇲', label: 'Jamaica', value: '+1', iso: 'JM' },
    { flag: '🇯🇵', label: 'Japan', value: '+81', iso: 'JP' },
    { flag: '🇯🇪', label: 'Jersey', value: '+44', iso: 'JE' },
    { flag: '🇯🇴', label: 'Jordan', value: '+962', iso: 'JO' },
    { flag: '🇰🇿', label: 'Kazakhstan', value: '+7', iso: 'KZ' },
    { flag: '🇰🇪', label: 'Kenya', value: '+254', iso: 'KE' },
    { flag: '🇰🇮', label: 'Kiribati', value: '+686', iso: 'KI' },
    { flag: '🇽🇰', label: 'Kosovo', value: '+383', iso: 'XK' },
    { flag: '🇰🇼', label: 'Kuwait', value: '+965', iso: 'KW' },
    { flag: '🇰🇬', label: 'Kyrgyzstan', value: '+996', iso: 'KG' },
    { flag: '🇱🇦', label: 'Laos', value: '+856', iso: 'LA' },
    { flag: '🇱🇻', label: 'Latvia', value: '+371', iso: 'LV' },
    { flag: '🇱🇧', label: 'Lebanon', value: '+961', iso: 'LB' },
    { flag: '🇱🇸', label: 'Lesotho', value: '+266', iso: 'LS' },
    { flag: '🇱🇷', label: 'Liberia', value: '+231', iso: 'LR' },
    { flag: '🇱🇾', label: 'Libya', value: '+218', iso: 'LY' },
    { flag: '🇱🇮', label: 'Liechtenstein', value: '+423', iso: 'LI' },
    { flag: '🇱🇹', label: 'Lithuania', value: '+370', iso: 'LT' },
    { flag: '🇱🇺', label: 'Luxembourg', value: '+352', iso: 'LU' },
    { flag: '🇲🇴', label: 'Macau', value: '+853', iso: 'MO' },
    { flag: '🇲🇬', label: 'Madagascar', value: '+261', iso: 'MG' },
    { flag: '🇲🇼', label: 'Malawi', value: '+265', iso: 'MW' },
    { flag: '🇲🇾', label: 'Malaysia', value: '+60', iso: 'MY' },
    { flag: '🇲🇻', label: 'Maldives', value: '+960', iso: 'MV' },
    { flag: '🇲🇱', label: 'Mali', value: '+223', iso: 'ML' },
    { flag: '🇲🇹', label: 'Malta', value: '+356', iso: 'MT' },
    { flag: '🇲🇭', label: 'Marshall Islands', value: '+692', iso: 'MH' },
    { flag: '🇲🇶', label: 'Martinique', value: '+596', iso: 'MQ' },
    { flag: '🇲🇷', label: 'Mauritania', value: '+222', iso: 'MR' },
    { flag: '🇲🇺', label: 'Mauritius', value: '+230', iso: 'MU' },
    { flag: '🇲🇽', label: 'Mexico', value: '+52', iso: 'MX' },
    { flag: '🇫🇲', label: 'Micronesia', value: '+691', iso: 'FM' },
    { flag: '🇲🇩', label: 'Moldova', value: '+373', iso: 'MD' },
    { flag: '🇲🇨', label: 'Monaco', value: '+377', iso: 'MC' },
    { flag: '🇲🇳', label: 'Mongolia', value: '+976', iso: 'MN' },
    { flag: '🇲🇪', label: 'Montenegro', value: '+382', iso: 'ME' },
    { flag: '🇲🇸', label: 'Montserrat', value: '+1', iso: 'MS' },
    { flag: '🇲🇦', label: 'Morocco', value: '+212', iso: 'MA' },
    { flag: '🇲🇿', label: 'Mozambique', value: '+258', iso: 'MZ' },
    { flag: '🇲🇲', label: 'Myanmar', value: '+95', iso: 'MM' },
    { flag: '🇳🇦', label: 'Namibia', value: '+264', iso: 'NA' },
    { flag: '🇳🇷', label: 'Nauru', value: '+674', iso: 'NR' },
    { flag: '🇳🇵', label: 'Nepal', value: '+977', iso: 'NP' },
    { flag: '🇳🇱', label: 'Netherlands', value: '+31', iso: 'NL' },
    { flag: '🇳🇨', label: 'New Caledonia', value: '+687', iso: 'NC' },
    { flag: '🇳🇿', label: 'New Zealand', value: '+64', iso: 'NZ' },
    { flag: '🇳🇮', label: 'Nicaragua', value: '+505', iso: 'NI' },
    { flag: '🇳🇪', label: 'Niger', value: '+227', iso: 'NE' },
    { flag: '🇳🇬', label: 'Nigeria', value: '+234', iso: 'NG' },
    { flag: '🇳🇺', label: 'Niue', value: '+683', iso: 'NU' },
    { flag: '🇰🇵', label: 'North Korea', value: '+850', iso: 'KP' },
    { flag: '🇲🇰', label: 'North Macedonia', value: '+389', iso: 'MK' },
    { flag: '🇲🇵', label: 'Northern Mariana Islands', value: '+1', iso: 'MP' },
    { flag: '🇳🇴', label: 'Norway', value: '+47', iso: 'NO' },
    { flag: '🇴🇲', label: 'Oman', value: '+968', iso: 'OM' },
    { flag: '🇵🇰', label: 'Pakistan', value: '+92', iso: 'PK' },
    { flag: '🇵🇼', label: 'Palau', value: '+680', iso: 'PW' },
    { flag: '🇵🇸', label: 'Palestine', value: '+970', iso: 'PS' },
    { flag: '🇵🇦', label: 'Panama', value: '+507', iso: 'PA' },
    { flag: '🇵🇬', label: 'Papua New Guinea', value: '+675', iso: 'PG' },
    { flag: '🇵🇾', label: 'Paraguay', value: '+595', iso: 'PY' },
    { flag: '🇵🇪', label: 'Peru', value: '+51', iso: 'PE' },
    { flag: '🇵🇭', label: 'Philippines', value: '+63', iso: 'PH' },
    { flag: '🇵🇱', label: 'Poland', value: '+48', iso: 'PL' },
    { flag: '🇵🇹', label: 'Portugal', value: '+351', iso: 'PT' },
    { flag: '🇵🇷', label: 'Puerto Rico', value: '+1', iso: 'PR' },
    { flag: '🇶🇦', label: 'Qatar', value: '+974', iso: 'QA' },
    { flag: '🇷🇪', label: 'Reunion', value: '+262', iso: 'RE' },
    { flag: '🇷🇴', label: 'Romania', value: '+40', iso: 'RO' },
    { flag: '🇷🇺', label: 'Russia', value: '+7', iso: 'RU' },
    { flag: '🇷🇼', label: 'Rwanda', value: '+250', iso: 'RW' },
    { flag: '🇼🇸', label: 'Samoa', value: '+685', iso: 'WS' },
    { flag: '🇸🇲', label: 'San Marino', value: '+378', iso: 'SM' },
    { flag: '🇸🇹', label: 'Sao Tome and Principe', value: '+239', iso: 'ST' },
    { flag: '🇸🇦', label: 'Saudi Arabia', value: '+966', iso: 'SA' },
    { flag: '🇸🇳', label: 'Senegal', value: '+221', iso: 'SN' },
    { flag: '🇷🇸', label: 'Serbia', value: '+381', iso: 'RS' },
    { flag: '🇸🇨', label: 'Seychelles', value: '+248', iso: 'SC' },
    { flag: '🇸🇱', label: 'Sierra Leone', value: '+232', iso: 'SL' },
    { flag: '🇸🇬', label: 'Singapore', value: '+65', iso: 'SG' },
    { flag: '🇸🇽', label: 'Sint Maarten', value: '+1', iso: 'SX' },
    { flag: '🇸🇰', label: 'Slovakia', value: '+421', iso: 'SK' },
    { flag: '🇸🇮', label: 'Slovenia', value: '+386', iso: 'SI' },
    { flag: '🇸🇧', label: 'Solomon Islands', value: '+677', iso: 'SB' },
    { flag: '🇸🇴', label: 'Somalia', value: '+252', iso: 'SO' },
    { flag: '🇿🇦', label: 'South Africa', value: '+27', iso: 'ZA' },
    { flag: '🇰🇷', label: 'South Korea', value: '+82', iso: 'KR' },
    { flag: '🇸🇸', label: 'South Sudan', value: '+211', iso: 'SS' },
    { flag: '🇪🇸', label: 'Spain', value: '+34', iso: 'ES' },
    { flag: '🇱🇰', label: 'Sri Lanka', value: '+94', iso: 'LK' },
    { flag: '🇰🇳', label: 'St. Kitts and Nevis', value: '+1', iso: 'KN' },
    { flag: '🇱🇨', label: 'St. Lucia', value: '+1', iso: 'LC' },
    { flag: '🇻🇨', label: 'St. Vincent and the Grenadines', value: '+1', iso: 'VC' },
    { flag: '🇸🇩', label: 'Sudan', value: '+249', iso: 'SD' },
    { flag: '🇸🇷', label: 'Suriname', value: '+597', iso: 'SR' },
    { flag: '🇸🇪', label: 'Sweden', value: '+46', iso: 'SE' },
    { flag: '🇨🇭', label: 'Switzerland', value: '+41', iso: 'CH' },
    { flag: '🇸🇾', label: 'Syria', value: '+963', iso: 'SY' },
    { flag: '🇹🇼', label: 'Taiwan', value: '+886', iso: 'TW' },
    { flag: '🇹🇯', label: 'Tajikistan', value: '+992', iso: 'TJ' },
    { flag: '🇹🇿', label: 'Tanzania', value: '+255', iso: 'TZ' },
    { flag: '🇹🇭', label: 'Thailand', value: '+66', iso: 'TH' },
    { flag: '🇹🇱', label: 'Timor-Leste', value: '+670', iso: 'TL' },
    { flag: '🇹🇬', label: 'Togo', value: '+228', iso: 'TG' },
    { flag: '🇹🇴', label: 'Tonga', value: '+676', iso: 'TO' },
    { flag: '🇹🇹', label: 'Trinidad and Tobago', value: '+1', iso: 'TT' },
    { flag: '🇹🇳', label: 'Tunisia', value: '+216', iso: 'TN' },
    { flag: '🇹🇷', label: 'Turkey', value: '+90', iso: 'TR' },
    { flag: '🇹🇲', label: 'Turkmenistan', value: '+993', iso: 'TM' },
    { flag: '🇹🇨', label: 'Turks and Caicos Islands', value: '+1', iso: 'TC' },
    { flag: '🇹🇻', label: 'Tuvalu', value: '+688', iso: 'TV' },
    { flag: '🇺🇬', label: 'Uganda', value: '+256', iso: 'UG' },
    { flag: '🇺🇦', label: 'Ukraine', value: '+380', iso: 'UA' },
    { flag: '🇦🇪', label: 'United Arab Emirates', value: '+971', iso: 'AE' },
    { flag: '🇬🇧', label: 'United Kingdom', value: '+44', iso: 'GB' },
    { flag: '🇺🇸', label: 'United States', value: '+1', iso: 'US' },
    { flag: '🇺🇾', label: 'Uruguay', value: '+598', iso: 'UY' },
    { flag: '🇺🇿', label: 'Uzbekistan', value: '+998', iso: 'UZ' },
    { flag: '🇻🇺', label: 'Vanuatu', value: '+678', iso: 'VU' },
    { flag: '🇻🇦', label: 'Vatican City', value: '+379', iso: 'VA' },
    { flag: '🇻🇪', label: 'Venezuela', value: '+58', iso: 'VE' },
    { flag: '🇻🇳', label: 'Vietnam', value: '+84', iso: 'VN' },
    { flag: '🇻🇮', label: 'Virgin Islands, U.S.', value: '+1', iso: 'VI' },
    { flag: '🇾🇪', label: 'Yemen', value: '+967', iso: 'YE' },
    { flag: '🇿🇲', label: 'Zambia', value: '+260', iso: 'ZM' },
    { flag: '🇿🇼', label: 'Zimbabwe', value: '+263', iso: 'ZW' },
];

const joinMonthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const joinWeekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function initJoinRequestModal(root) {
    if (root.dataset.ready === 'true') {
        return;
    }

    root.dataset.ready = 'true';

    const form = root.querySelector('[data-join-form]');
    const submitButton = root.querySelector('[data-join-submit]');
    const messageBox = root.querySelector('[data-form-message]');
    const applicantInput = root.querySelector('[data-applicant-type]');
    const sectionOrder = ['basic', 'eligibility', 'professional', 'experience', 'credentials'];
    const sections = Array.from(root.querySelectorAll('[data-section]'));
    const registries = {
        selects: [],
        dates: [],
        radios: [],
        uploads: [],
    };

    let activeSection = 'basic';
    let selectedType = 'skilled';
    let closeTimer = null;
    let isSubmitting = false;

    const closeFloatingControls = (except = null) => {
        root.querySelectorAll('.join-custom-select.is-open, .join-date.is-open').forEach((control) => {
            if (control !== except) {
                control.classList.remove('is-open');
            }
        });
    };

    initCustomSelects(root, registries, closeFloatingControls);
    initDatePickers(root, registries, closeFloatingControls);
    initRadioGroups(root, registries);
    initUploads(root, registries);

    document.querySelectorAll('[data-join-open]').forEach((button) => {
        button.addEventListener('click', () => openModal(button.dataset.joinOpen || 'skilled'));
    });

    root.querySelectorAll('[data-join-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    root.querySelectorAll('[data-type-option]').forEach((button) => {
        button.addEventListener('click', () => setApplicantType(button.dataset.typeOption));
    });

    root.querySelectorAll('[data-section-toggle]').forEach((button) => {
        button.addEventListener('click', () => setSection(button.dataset.sectionToggle));
    });

    root.querySelectorAll('[data-next-section]').forEach((button) => {
        button.addEventListener('click', () => {
            const section = button.closest('[data-section]');
            if (!section || !validateScope(section)) {
                focusFirstError(section || form);
                return;
            }

            setSection(button.dataset.nextSection);
        });
    });

    form.addEventListener('submit', submitForm);

    document.addEventListener('keydown', (event) => {
        if (!root.hidden && event.key === 'Escape') {
            closeModal();
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.hidden && !event.target.closest('.join-custom-select') && !event.target.closest('.join-date')) {
            closeFloatingControls();
        }
    });

    function openModal(type) {
        if (root.dataset.isPage === 'true') return;
        window.clearTimeout(closeTimer);
        resetForm(normalizeApplicantType(type));
        root.hidden = false;
        root.setAttribute('aria-hidden', 'false');
        document.body.classList.add('join-modal-locked');

        requestAnimationFrame(() => {
            root.classList.add('is-open');
        });
    }

    function closeModal() {
        if (root.hidden || root.dataset.isPage === 'true') {
            return;
        }

        root.classList.remove('is-open');
        root.setAttribute('aria-hidden', 'true');
        closeFloatingControls();
        closeTimer = window.setTimeout(() => {
            root.hidden = true;
            document.body.classList.remove('join-modal-locked');
        }, 300);
    }

    // Auto-initialize if it's a dedicated page
    if (root.dataset.isPage === 'true') {
        const urlParams = new URLSearchParams(window.location.search);
        const type = urlParams.get('type') || 'skilled';
        setApplicantType(type);
        setSection('basic');
    }

    function normalizeApplicantType(type) {
        return type === 'non-skilled' ? 'non-skilled' : 'skilled';
    }

    function setApplicantType(type) {
        selectedType = normalizeApplicantType(type);
        applicantInput.value = selectedType;
        root.querySelectorAll('[data-type-option]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.typeOption === selectedType);
        });
    }

    function setSection(sectionKey, shouldScroll = true) {
        activeSection = sectionOrder.includes(sectionKey) ? sectionKey : 'basic';

        sections.forEach((section) => {
            const isActive = section.dataset.section === activeSection;
            section.classList.toggle('is-active', isActive);
            const toggle = section.querySelector('[data-section-toggle]');
            if (toggle) {
                toggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            }
        });

        updateSubmitState();

        if (shouldScroll) {
            const section = root.querySelector(`[data-section="${activeSection}"]`);
            section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function resetForm(type, options = {}) {
        form.reset();
        clearAllErrors();
        closeFloatingControls();

        if (!options.keepMessage) {
            setMessage('', '');
        }

        setApplicantType(type);
        registries.selects.forEach((control) => control.reset());
        registries.dates.forEach((control) => control.reset());
        registries.radios.forEach((control) => control.reset());
        registries.uploads.forEach((control) => control.reset());
        setSubmitting(false);
        setSection('basic', false);
    }

    function updateSubmitState() {
        submitButton.disabled = isSubmitting || activeSection !== 'credentials';
    }

    function setSubmitting(value) {
        isSubmitting = value;
        submitButton.textContent = value ? 'Submitting...' : 'Submit Form';
        updateSubmitState();
    }

    async function submitForm(event) {
        event.preventDefault();
        setMessage('', '');

        if (!validateScope(form)) {
            const invalidSection = firstInvalidSection();
            if (invalidSection) {
                setSection(invalidSection);
            }
            focusFirstError(form);
            setMessage('error', 'Please complete the highlighted fields before submitting.');
            return;
        }

        setSubmitting(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new FormData(form),
            });

            const payload = await response.json().catch(() => ({}));

            if (response.ok) {
                const currentType = selectedType;
                setMessage('success', payload.message || 'Application submitted successfully.');
                resetForm(currentType, { keepMessage: true });
                window.setTimeout(closeModal, 1400);
                return;
            }

            if (response.status === 422 && payload.errors) {
                applyServerErrors(payload.errors);
                const invalidSection = firstInvalidSection();
                if (invalidSection) {
                    setSection(invalidSection);
                }
                focusFirstError(form);
                setMessage('error', 'Please review the highlighted fields.');
                return;
            }

            setMessage('error', payload.message || 'We could not submit the form. Please try again.');
        } catch (error) {
            setMessage('error', 'We could not submit the form. Please check your connection and try again.');
        } finally {
            setSubmitting(false);
        }
    }

    function validateScope(scope) {
        let isValid = true;
        const inputs = Array.from(scope.querySelectorAll('input[name], textarea[name]'))
            .filter((input) => input.name !== '_token' && !input.disabled);

        inputs.forEach((input) => {
            if (!validateInput(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    function validateInput(input) {
        const value = getInputValue(input);
        const label = input.dataset.label || humanizeName(input.name);
        const required = isInputRequired(input);

        clearFieldError(input);

        if (input.type === 'checkbox') {
            if (required && !input.checked) {
                setFieldError(input, `${label} is required.`);
                return false;
            }
            return true;
        }

        if (input.type === 'file') {
            return validateFileInput(input, label, required);
        }

        if (required && value === '') {
            setFieldError(input, `${label} is required.`);
            return false;
        }

        if (value === '') {
            return true;
        }

        if (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            setFieldError(input, 'Enter a valid email address.');
            return false;
        }

        if (input.dataset.phone !== undefined) {
            const digits = value.replace(/\D/g, '');
            if (digits.length < 7 || digits.length > 15) {
                setFieldError(input, 'Enter a valid phone number.');
                return false;
            }
        }

        if (input.dataset.number !== undefined) {
            const number = Number(value);
            if (!Number.isFinite(number) || number <= 0) {
                setFieldError(input, 'Enter a valid amount.');
                return false;
            }
        }

        if (input.dataset.dateRole === 'dob') {
            const selectedDate = new Date(`${value}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate >= today) {
                setFieldError(input, 'Date of birth must be in the past.');
                return false;
            }
        }

        return true;
    }

    function validateFileInput(input, label, required) {
        const files = Array.from(input.files || []);
        const kind = input.dataset.fileKind;
        const maxSize = kind === 'signature' ? 5 * 1024 * 1024 : 20 * 1024 * 1024;
        const allowedExtensions = kind === 'signature'
            ? ['jpg', 'jpeg', 'png', 'webp']
            : ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'];

        if (required && files.length === 0) {
            setFieldError(input, `${label} is required.`);
            return false;
        }

        if (kind === 'signature' && files.length > 1) {
            setFieldError(input, 'Upload only one signature image.');
            return false;
        }

        for (const file of files) {
            const extension = file.name.split('.').pop()?.toLowerCase() || '';
            if (!allowedExtensions.includes(extension)) {
                setFieldError(input, `${file.name} is not an accepted file type.`);
                return false;
            }

            if (file.size > maxSize) {
                setFieldError(input, `${file.name} is too large.`);
                return false;
            }
        }

        return true;
    }

    function isInputRequired(input) {
        if (input.dataset.requiredIf) {
            const [fieldName, expectedValue] = input.dataset.requiredIf.split(':');
            const dependency = form.elements[fieldName];
            return dependency?.value === expectedValue;
        }

        return input.dataset.required !== undefined;
    }

    function getInputValue(input) {
        if (input.type === 'file') {
            return input.files?.length ? 'files' : '';
        }

        return String(input.value || '').trim();
    }

    function setFieldError(input, message) {
        const holder = input.closest('.join-field')
            || input.closest('.join-upload-group')
            || input.closest('.join-disclaimer-block')
            || input.closest('.join-radio-group-wrap');

        holder?.classList.add('has-error');
        const error = holder?.querySelector('[data-error]');
        if (error) {
            error.textContent = message;
        }
    }

    function clearFieldError(input) {
        const holder = input.closest('.join-field')
            || input.closest('.join-upload-group')
            || input.closest('.join-disclaimer-block')
            || input.closest('.join-radio-group-wrap');

        if (input.type === 'checkbox') {
            const block = input.closest('.join-disclaimer-block');
            const hasUncheckedRequiredBox = block
                ? Array.from(block.querySelectorAll('input[type="checkbox"][data-required]')).some((checkbox) => !checkbox.checked)
                : false;

            if (hasUncheckedRequiredBox) {
                return;
            }
        }

        holder?.classList.remove('has-error');
        const error = holder?.querySelector('[data-error]');
        if (error) {
            error.textContent = '';
        }
    }

    function clearAllErrors() {
        root.querySelectorAll('.has-error').forEach((element) => element.classList.remove('has-error'));
        root.querySelectorAll('[data-error]').forEach((element) => {
            element.textContent = '';
        });
    }

    function firstInvalidSection() {
        const invalid = root.querySelector('.has-error');
        return invalid?.closest('[data-section]')?.dataset.section || null;
    }

    function focusFirstError(scope) {
        const holder = scope?.querySelector('.has-error');
        const focusTarget = holder?.querySelector('input:not([type="hidden"]), textarea, button');
        focusTarget?.focus({ preventScroll: true });
    }

    function applyServerErrors(errors) {
        clearAllErrors();

        Object.entries(errors).forEach(([key, messages]) => {
            const input = findInputByLaravelKey(key);
            const message = Array.isArray(messages) ? messages[0] : String(messages);

            if (input) {
                setFieldError(input, message);
            } else {
                setMessage('error', message);
            }
        });
    }

    function findInputByLaravelKey(key) {
        const candidates = new Set([key]);
        const parts = key.split('.');

        if (parts.length > 1) {
            let bracketName = parts.shift();
            parts.forEach((part) => {
                bracketName += `[${part}]`;
            });
            candidates.add(bracketName);
            candidates.add(`${key.split('.')[0]}[]`);
        }

        return Array.from(candidates)
            .map((name) => form.querySelector(`[name="${cssEscape(name)}"]`))
            .find(Boolean) || null;
    }

    function setMessage(type, text) {
        messageBox.classList.remove('is-success', 'is-error');
        messageBox.textContent = text;

        if (type === 'success') {
            messageBox.classList.add('is-success');
        }

        if (type === 'error') {
            messageBox.classList.add('is-error');
        }
    }
}

function initCustomSelects(root, registries, closeFloatingControls) {
    root.querySelectorAll('[data-custom-select]').forEach((select) => {
        const options = select.dataset.selectOptions === 'countries' ? joinCountryOptions : joinTitleOptions;
        const hidden = document.createElement('input');
        const button = document.createElement('button');
        const label = document.createElement('span');
        const caret = document.createElement('span');
        const menu = document.createElement('div');
        const list = document.createElement('div');
        const defaultValue = select.dataset.default;
        let selected = pickDefaultOption(options, defaultValue, select.dataset.selectOptions);

        hidden.type = 'hidden';
        hidden.name = select.dataset.name;
        hidden.dataset.label = select.dataset.label || humanizeName(hidden.name);
        if (select.dataset.required !== undefined) {
            hidden.dataset.required = '';
        }

        button.type = 'button';
        button.className = 'join-select-button';
        label.className = 'join-select-label';
        caret.className = 'join-select-caret';
        caret.textContent = '▾';
        menu.className = 'join-select-menu';

        button.append(label, caret);

        let search = null;
        if (options.length > 12) {
            search = document.createElement('input');
            search.type = 'search';
            search.className = 'join-select-search';
            search.placeholder = 'Search country';
            search.addEventListener('input', () => renderOptions(search.value));
            search.addEventListener('click', (event) => event.stopPropagation());
            menu.append(search);
        }

        menu.append(list);
        select.append(hidden, button, menu);

        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = !select.classList.contains('is-open');
            closeFloatingControls(select);
            select.classList.toggle('is-open', willOpen);

            if (willOpen && search) {
                window.setTimeout(() => search.focus(), 0);
            }
        });

        function renderOptions(filter = '') {
            const normalizedFilter = filter.trim().toLowerCase();
            list.innerHTML = '';

            options
                .filter((option) => {
                    const haystack = `${option.label} ${option.value} ${option.iso || ''}`.toLowerCase();
                    return haystack.includes(normalizedFilter);
                })
                .forEach((option) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'join-select-option';
                    item.classList.toggle('is-selected', option === selected);
                    if (option.iso) {
                        item.innerHTML = `<img src="https://flagcdn.com/${option.iso.toLowerCase()}.svg" width="20" alt="${option.iso}" class="join-select-flag" style="display:inline-block; margin-right:6px; vertical-align:middle;"> ${option.label} ${option.value}`;
                    } else {
                        item.textContent = option.label;
                    }
                    item.addEventListener('click', (event) => {
                        event.stopPropagation();
                        setSelected(option);
                        select.classList.remove('is-open');
                    });
                    list.append(item);
                });
        }

        function setSelected(option) {
            selected = option;
            hidden.value = option.value;
            if (option.iso) {
                label.innerHTML = `<img src="https://flagcdn.com/${option.iso.toLowerCase()}.svg" width="20" alt="${option.iso}" class="join-select-flag" style="display:inline-block; margin-right:6px; vertical-align:middle;"> ${option.value}`;
            } else {
                label.textContent = option.label;
            }
            renderOptions(search?.value || '');
        }

        setSelected(selected);
        renderOptions();

        registries.selects.push({
            reset() {
                if (search) {
                    search.value = '';
                }
                setSelected(pickDefaultOption(options, defaultValue, select.dataset.selectOptions));
                select.classList.remove('is-open');
            },
        });
    });
}

function pickDefaultOption(options, defaultValue, optionType) {
    if (optionType === 'countries' && defaultValue === '+1') {
        return options.find((option) => option.iso === 'US') || options[0];
    }

    return options.find((option) => option.value === defaultValue) || options[0];
}

function initDatePickers(root, registries, closeFloatingControls) {
    root.querySelectorAll('[data-date-picker]').forEach((picker) => {
        const hidden = document.createElement('input');
        const button = document.createElement('button');
        const icon = document.createElement('img');
        const label = document.createElement('span');
        const menu = document.createElement('div');
        let viewDate = new Date();
        let selectedValue = '';

        hidden.type = 'hidden';
        hidden.name = picker.dataset.name;
        hidden.dataset.label = picker.dataset.label || humanizeName(hidden.name);
        if (picker.dataset.required !== undefined) {
            hidden.dataset.required = '';
        }
        if (picker.dataset.dateRole) {
            hidden.dataset.dateRole = picker.dataset.dateRole;
        }

        button.type = 'button';
        button.className = 'join-date-button';
        icon.className = 'join-date-icon';
        icon.src = '/assets/icons/Property%201%3DCalendar.svg';
        icon.alt = '';
        label.className = 'join-date-label';
        label.textContent = 'DD-MM-YY';
        menu.className = 'join-date-menu';
        button.append(icon, label);
        picker.append(hidden, button, menu);

        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = !picker.classList.contains('is-open');
            closeFloatingControls(picker);
            picker.classList.toggle('is-open', willOpen);
            renderCalendar();
        });

        function renderCalendar() {
            menu.innerHTML = '';
            const year = viewDate.getFullYear();
            const month = viewDate.getMonth();
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const todayIso = toIsoDate(new Date());

            const top = document.createElement('div');
            top.className = 'join-date-top';

            const previous = document.createElement('button');
            previous.type = 'button';
            previous.className = 'join-date-nav';
            previous.textContent = '‹';
            previous.addEventListener('click', (event) => {
                event.stopPropagation();
                viewDate = new Date(year, month - 1, 1);
                renderCalendar();
            });

            const current = document.createElement('div');
            current.className = 'join-date-title';

            const monthSpan = document.createElement('span');
            monthSpan.textContent = joinMonthNames[month];

            const yearSelect = document.createElement('select');
            yearSelect.className = 'join-date-year-select';
            const currentYear = new Date().getFullYear();
            const startYear = picker.dataset.dateRole === 'dob' ? 1930 : 1960;
            const endYear = currentYear + 25;

            for (let y = startYear; y <= endYear; y += 1) {
                const opt = document.createElement('option');
                opt.value = String(y);
                opt.textContent = String(y);
                if (y === year) {
                    opt.selected = true;
                }
                yearSelect.appendChild(opt);
            }

            yearSelect.addEventListener('change', (event) => {
                event.stopPropagation();
                const selectedYear = parseInt(event.target.value, 10);
                viewDate = new Date(selectedYear, month, 1);
                renderCalendar();
            });

            current.append(monthSpan, yearSelect);

            const next = document.createElement('button');
            next.type = 'button';
            next.className = 'join-date-nav';
            next.textContent = '›';
            next.addEventListener('click', (event) => {
                event.stopPropagation();
                viewDate = new Date(year, month + 1, 1);
                renderCalendar();
            });

            top.append(previous, current, next);

            const weekdays = document.createElement('div');
            weekdays.className = 'join-date-weekdays';
            joinWeekdays.forEach((weekday) => {
                const item = document.createElement('span');
                item.textContent = weekday;
                weekdays.append(item);
            });

            const days = document.createElement('div');
            days.className = 'join-date-days';

            for (let index = 0; index < firstDay; index += 1) {
                const blank = document.createElement('span');
                blank.className = 'join-date-blank';
                days.append(blank);
            }

            for (let day = 1; day <= daysInMonth; day += 1) {
                const date = new Date(year, month, day);
                const iso = toIsoDate(date);
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'join-date-day';
                item.textContent = String(day);
                item.classList.toggle('is-selected', iso === selectedValue);
                item.classList.toggle('is-today', iso === todayIso);
                item.addEventListener('click', (event) => {
                    event.stopPropagation();
                    setSelectedDate(date);
                    picker.classList.remove('is-open');
                });
                days.append(item);
            }

            menu.append(top, weekdays, days);
        }

        function setSelectedDate(date) {
            selectedValue = toIsoDate(date);
            hidden.value = selectedValue;
            label.textContent = formatDateForDisplay(date);
            button.classList.add('has-value');
            renderCalendar();
        }

        renderCalendar();

        registries.dates.push({
            reset() {
                selectedValue = '';
                hidden.value = '';
                label.textContent = 'DD-MM-YY';
                button.classList.remove('has-value');
                viewDate = new Date();
                picker.classList.remove('is-open');
                renderCalendar();
            },
        });
    });
}

function initRadioGroups(root, registries) {
    root.querySelectorAll('[data-radio-group]').forEach((group) => {
        const hidden = document.createElement('input');
        const buttons = Array.from(group.querySelectorAll('[data-radio-value]'));

        hidden.type = 'hidden';
        hidden.name = group.dataset.name;
        hidden.dataset.label = group.dataset.label || humanizeName(hidden.name);
        if (group.dataset.required !== undefined) {
            hidden.dataset.required = '';
        }

        group.prepend(hidden);

        buttons.forEach((button) => {
            button.addEventListener('click', () => setSelected(button.dataset.radioValue));
        });

        function setSelected(value) {
            hidden.value = value;
            buttons.forEach((button) => {
                button.classList.toggle('is-selected', button.dataset.radioValue === value);
            });
        }

        registries.radios.push({
            reset() {
                hidden.value = '';
                buttons.forEach((button) => button.classList.remove('is-selected'));
            },
        });
    });
}

function initUploads(root, registries) {
    root.querySelectorAll('[data-upload-zone]').forEach((zone) => {
        const input = zone.querySelector('[data-upload-input]');
        const preview = zone.querySelector('[data-upload-preview]');
        let objectUrls = [];
        let currentFiles = [];

        input.addEventListener('change', () => {
            mergeFiles(input.files);
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            zone.addEventListener(eventName, (event) => {
                event.preventDefault();
                zone.classList.add('is-dragging');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            zone.addEventListener(eventName, (event) => {
                event.preventDefault();
                zone.classList.remove('is-dragging');
            });
        });

        zone.addEventListener('drop', (event) => {
            const files = Array.from(event.dataTransfer.files || []);
            mergeFiles(files);
        });

        function mergeFiles(newFiles) {
            const kind = input.dataset.fileKind;
            const maxCount = kind === 'signature' ? 1 : 3;
            
            Array.from(newFiles).forEach(file => {
                if (currentFiles.length < maxCount && !currentFiles.some(f => f.name === file.name && f.size === file.size)) {
                    currentFiles.push(file);
                }
            });
            
            syncInput();
            renderFiles();
        }

        function removeFile(index) {
            currentFiles.splice(index, 1);
            syncInput();
            renderFiles();
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function syncInput() {
            const dataTransfer = new DataTransfer();
            currentFiles.forEach((file) => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        }

        function renderFiles() {
            objectUrls.forEach((url) => URL.revokeObjectURL(url));
            objectUrls = [];
            preview.innerHTML = '';

            zone.classList.toggle('has-files', currentFiles.length > 0);

            currentFiles.forEach((file, index) => {
                const card = document.createElement('div');
                const meta = document.createElement('div');
                const title = document.createElement('strong');
                const size = document.createElement('span');
                const extension = file.name.split('.').pop()?.toUpperCase() || 'FILE';

                card.className = 'join-upload-card';
                card.style.position = 'relative';
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'join-upload-remove';
                removeBtn.innerHTML = '&times;';
                removeBtn.setAttribute('aria-label', 'Remove file');
                removeBtn.addEventListener('click', (event) => {
                    event.stopPropagation();
                    event.preventDefault();
                    removeFile(index);
                });

                if (file.type.startsWith('image/')) {
                    const image = document.createElement('img');
                    const url = URL.createObjectURL(file);
                    objectUrls.push(url);
                    image.src = url;
                    image.alt = file.name;
                    card.append(image);
                } else {
                    const icon = document.createElement('div');
                    icon.className = 'join-upload-file-icon';
                    icon.textContent = extension;
                    card.append(icon);
                }

                title.textContent = file.name;
                size.textContent = formatFileSize(file.size);
                meta.append(title, size);
                card.append(meta, removeBtn);
                preview.append(card);
            });
        }

        registries.uploads.push({
            reset() {
                currentFiles = [];
                syncInput();
                renderFiles();
            },
        });
    });
}

function toIsoDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateForDisplay(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = String(date.getFullYear()).slice(-2);
    return `${day}-${month}-${year}`;
}

function formatFileSize(bytes) {
    if (bytes < 1024 * 1024) {
        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function humanizeName(name) {
    return name
        .replace(/\[\]/g, '')
        .replace(/\[/g, ' ')
        .replace(/\]/g, '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase())
        .trim();
}

function cssEscape(value) {
    if (window.CSS?.escape) {
        return CSS.escape(value);
    }

    return value.replace(/["\\]/g, '\\$&');
}

function initNewsletterForm(form) {
    if (form.dataset.ready === 'true') {
        return;
    }

    form.dataset.ready = 'true';

    const submitButton = form.querySelector('[data-newsletter-submit]');
    const label = form.querySelector('[data-newsletter-label]');
    const loading = form.querySelector('[data-newsletter-loading]');
    const feedback = form.parentElement?.querySelector('[data-newsletter-feedback]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setNewsletterFeedback(feedback, '', '');
        setNewsletterSubmitting(submitButton, label, loading, true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new FormData(form),
            });

            const payload = await response.json().catch(() => ({}));

            if (response.ok) {
                form.reset();
                setNewsletterFeedback(feedback, 'success', payload.message || 'Thanks for subscribing. You are on the list.');
                return;
            }

            if (response.status === 422 && payload.errors) {
                const firstError = Object.values(payload.errors).flat()[0];
                setNewsletterFeedback(feedback, 'error', firstError || 'Please enter a valid email address.');
                return;
            }

            setNewsletterFeedback(feedback, 'error', payload.message || 'We could not subscribe you right now. Please try again.');
        } catch (error) {
            setNewsletterFeedback(feedback, 'error', 'We could not subscribe you right now. Please check your connection and try again.');
        } finally {
            setNewsletterSubmitting(submitButton, label, loading, false);
        }
    });
}

function setNewsletterSubmitting(button, label, loading, isSubmitting) {
    if (button) {
        button.disabled = isSubmitting;
    }

    label?.classList.toggle('hidden', isSubmitting);
    loading?.classList.toggle('hidden', !isSubmitting);
}

function setNewsletterFeedback(feedback, type, message) {
    if (!feedback) {
        return;
    }

    feedback.textContent = message;
    feedback.classList.remove('hidden', 'bg-primary-50', 'text-primary-300', 'bg-red-50', 'text-red-700');

    if (!message) {
        feedback.classList.add('hidden');
        return;
    }

    if (type === 'success') {
        feedback.classList.add('bg-primary-50', 'text-primary-300');
        return;
    }

    feedback.classList.add('bg-red-50', 'text-red-700');
}

function initCalendlyBridge() {
    if (window.WesthubCalendly?.ready) {
        return;
    }

    let activeAppointmentId = null;
    let activeScheduled = false;
    let lifecycleTimer = null;

    function open(url, appointmentId = null) {
        if (!url) {
            return;
        }

        activeAppointmentId = appointmentId || activeAppointmentId;
        activeScheduled = false;

        if (activeAppointmentId) {
            dispatchLivewire('calendlyOpened', {
                appointmentId: Number(activeAppointmentId),
            });
        }

        if (window.Calendly?.initPopupWidget) {
            window.Calendly.initPopupWidget({ url });
            watchCalendlyPopupLifecycle();
            return;
        }

        dispatchLivewire('calendlyOpenFailed', {
            appointmentId: Number(activeAppointmentId),
        });

        const opened = window.open(url, '_blank', 'noopener');
        if (!opened && activeAppointmentId) {
            dispatchLivewire('calendlyOpenFailed', {
                appointmentId: Number(activeAppointmentId),
            });
        }
    }

    function isCalendlyEvent(event) {
        return event.origin === 'https://calendly.com'
            && typeof event.data?.event === 'string'
            && event.data.event.startsWith('calendly.');
    }

    function dispatchLivewire(eventName, payload) {
        if (!window.Livewire?.dispatch || !payload?.appointmentId) {
            return;
        }

        window.Livewire.dispatch(eventName, payload);
    }

    function closeCalendlyPopup() {
        if (window.Calendly?.closePopupWidget) {
            window.Calendly.closePopupWidget();
            return;
        }

        document.querySelector('.calendly-popup-close')?.click();
    }

    function watchCalendlyPopupLifecycle() {
        window.clearInterval(lifecycleTimer);

        let overlaySeen = false;
        const startedAt = Date.now();

        lifecycleTimer = window.setInterval(() => {
            const overlay = document.querySelector('.calendly-overlay');

            if (overlay) {
                overlaySeen = true;
                return;
            }

            if (!overlaySeen && Date.now() - startedAt > 4500) {
                window.clearInterval(lifecycleTimer);
                dispatchLivewire('calendlyOpenFailed', {
                    appointmentId: Number(activeAppointmentId),
                });
                return;
            }

            if (overlaySeen && !overlay) {
                window.clearInterval(lifecycleTimer);
                dispatchLivewire('calendlyClosed', {
                    appointmentId: Number(activeAppointmentId),
                    scheduled: activeScheduled,
                });
            }
        }, 250);
    }

    window.WesthubCalendly = {
        ready: true,
        open,
    };

    window.addEventListener('westhub-open-calendly', (event) => {
        open(event.detail?.url, event.detail?.appointmentId);
    });

    window.addEventListener('message', (event) => {
        if (!isCalendlyEvent(event) || event.data.event !== 'calendly.event_scheduled') {
            return;
        }

        if (!activeAppointmentId) {
            return;
        }

        activeScheduled = true;

        dispatchLivewire('calendlyScheduled', {
            appointmentId: Number(activeAppointmentId),
            payload: event.data.payload || {},
        });

        window.setTimeout(closeCalendlyPopup, 900);
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-calendly-trigger]');

        if (!trigger) {
            return;
        }

        event.preventDefault();
        open(trigger.dataset.calendlyUrl, trigger.dataset.appointmentId ? Number(trigger.dataset.appointmentId) : null);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-join-request-modal]').forEach(initJoinRequestModal);
    document.querySelectorAll('[data-newsletter-form]').forEach(initNewsletterForm);
    initCalendlyBridge();
});

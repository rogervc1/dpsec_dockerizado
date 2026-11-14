export interface ExclusiveEventView {
    title: string;
    titleFirst: string;
    titleSecond: string;
    titleFirstColor: string;
    titleSecondColor: string;
    titleMaxChars: number;
    titleSizePercent: number;
    mobileTitleSizePercent: number;
    titleFont: string;
    titleLetterSpacing: number;
    subtitleFont: string;
    subtitleLetterSpacing: number;
    customFonts: Array<{ name: string; url: string }>;
    titlePosition: 'left' | 'center' | 'right';
    titleOffsetX: number;
    titleOffsetY: number;
    mobileTitlePosition: 'left' | 'center' | 'right';
    mobileTitleOffsetX: number;
    mobileTitleOffsetY: number;
    subtitle: string;
    subtitleColor: string;
    customAccent: string;
    date: string;
    eventDate: string;
    time: string;
    location: string;
    about: string;
    image: string;
    desktopCoverZoom: number;
    desktopCoverX: number;
    desktopCoverY: number;
    mobileCoverZoom: number;
    mobileCoverX: number;
    mobileCoverY: number;
    theme: string;
    status: string;
    targetDate: number | null;
}

export const EXCLUSIVE_FONT_PRESETS = ['Instrument Sans'];

export function splitExclusiveTitle(value: string): { first: string; second: string } {
    const words = String(value || '').trim().split(/\s+/).filter(Boolean);
    if (!words.length) return { first: 'Evento', second: 'especial' };
    let splitAt = Math.max(1, Math.ceil(words.length / 2));
    if (words.length > 1) {
        let shortestDifference = Number.POSITIVE_INFINITY;
        for (let index = 1; index < words.length; index += 1) {
            const firstLength = words.slice(0, index).join(' ').length;
            const secondLength = words.slice(index).join(' ').length;
            const difference = Math.abs(firstLength - secondLength);
            if (difference < shortestDifference) {
                shortestDifference = difference;
                splitAt = index;
            }
        }
    }
    return { first: words.slice(0, splitAt).join(' '), second: words.slice(splitAt).join(' ') };
}

const legacySizeToPercent = (size: unknown) => Math.min(150, Math.max(50, Math.round((Number(size) || 76) / 76 * 100)));

export function getExclusiveEventView(event: Record<string, any>): ExclusiveEventView {
    const settings = event.exclusive_page_settings || {};
    const legacyTitle = String(settings.title || event.title || 'Evento especial').trim();
    const splitTitle = splitExclusiveTitle(legacyTitle);
    const titleFirst = String(settings.title_first ?? splitTitle.first).trim();
    const titleSecond = String(settings.title_second ?? splitTitle.second).trim();
    const themeColors: Record<string, string> = { emerald: '#34d399', forest: '#4ade80', teal: '#2dd4bf', lime: '#a3e635', mint: '#6ee7b7' };
    const themeAccent = settings.theme_custom_color || themeColors[settings.theme] || themeColors.emerald;
    const title = [titleFirst, titleSecond].filter(Boolean).join(' ');
    const titleMaxChars = Math.max(titleFirst.length, titleSecond.length, 1);

    const eventDate = settings.event_date || (event.event_date ? String(event.event_date).slice(0, 10) : '');
    const parsedDate = eventDate ? new Date(`${eventDate}T00:00:00`) : null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const status = !parsedDate ? 'Proximos' : parsedDate < today ? 'Pasados' : parsedDate.getTime() === today.getTime() ? 'EnCurso' : 'Proximos';

    return {
        title,
        titleFirst,
        titleSecond: titleSecond || '\u00a0',
        titleFirstColor: settings.title_first_color || '#ffffff',
        titleSecondColor: settings.title_second_color || themeAccent,
        titleMaxChars,
        titleSizePercent: Math.min(150, Math.max(50, Number(settings.title_size_percent ?? legacySizeToPercent(settings.title_size)))),
        mobileTitleSizePercent: Math.min(150, Math.max(50, Number(settings.mobile_title_size_percent ?? 100))),
        titleFont: String(settings.title_font || EXCLUSIVE_FONT_PRESETS[0]),
        titleLetterSpacing: Number(settings.title_letter_spacing ?? 0),
        subtitleFont: String(settings.subtitle_font || EXCLUSIVE_FONT_PRESETS[0]),
        subtitleLetterSpacing: Number(settings.subtitle_letter_spacing ?? 0),
        customFonts: Array.isArray(settings.custom_fonts)
            ? settings.custom_fonts.flatMap((font: any) => font && typeof font.name === 'string' && (font.preview_url || font.url)
                ? [{ name: font.name, url: String(font.preview_url || font.url) }]
                : [])
            : [],
        titlePosition: settings.title_position || 'left',
        titleOffsetX: Number(settings.title_offset_x || 0),
        titleOffsetY: Number(settings.title_offset_y || 0),
        mobileTitlePosition: settings.mobile_title_position || settings.title_position || 'left',
        mobileTitleOffsetX: Number(settings.mobile_title_offset_x ?? settings.title_offset_x ?? 0),
        mobileTitleOffsetY: Number(settings.mobile_title_offset_y ?? settings.title_offset_y ?? 0),
        subtitle: settings.subtitle || '',
        subtitleColor: settings.subtitle_color || '#ffffff',
        customAccent: settings.theme_custom_color || '',
        date: parsedDate?.toLocaleDateString('es-PE', { day: 'numeric', month: 'long' }) || 'Fecha por confirmar',
        eventDate,
        time: settings.time ?? event.time ?? '',
        location: settings.location || event.location || '',
        about: settings.about || event.description || '',
        image: settings.desktop_cover || event.cover_image_path || event.image_path || '',
        desktopCoverZoom: Number(settings.desktop_cover_zoom || 1),
        desktopCoverX: Number(settings.desktop_cover_x ?? 50),
        desktopCoverY: Number(settings.desktop_cover_y ?? 50),
        mobileCoverZoom: Number(settings.mobile_cover_zoom || 1),
        mobileCoverX: Number(settings.mobile_cover_x ?? 50),
        mobileCoverY: Number(settings.mobile_cover_y ?? 50),
        theme: settings.theme || 'emerald',
        status,
        targetDate: parsedDate?.getTime() ?? null,
    };
}

export interface ExclusiveEventView {
    title: string;
    titleFirst: string;
    titleSecond: string;
    titleMaxChars: number;
    titleSize: number;
    titlePosition: 'left' | 'center' | 'right';
    titleOffsetX: number;
    titleOffsetY: number;
    subtitle: string;
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

export function getExclusiveEventView(event: Record<string, any>): ExclusiveEventView {
    const settings = event.exclusive_page_settings || {};
    const title = String(settings.title || event.title || 'Evento especial').trim();
    const words = title.split(/\s+/);
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

    const eventDate = settings.event_date || (event.event_date ? String(event.event_date).slice(0, 10) : '');
    const parsedDate = eventDate ? new Date(`${eventDate}T00:00:00`) : null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const status = !parsedDate ? 'Proximos' : parsedDate < today ? 'Pasados' : parsedDate.getTime() === today.getTime() ? 'EnCurso' : 'Proximos';

    return {
        title,
        titleFirst: words.slice(0, splitAt).join(' '),
        titleSecond: words.slice(splitAt).join(' ') || '\u00a0',
        titleMaxChars: Math.max(words.slice(0, splitAt).join(' ').length, words.slice(splitAt).join(' ').length, 1),
        titleSize: Math.min(112, Math.max(32, Number(settings.title_size || 76))),
        titlePosition: settings.title_position || 'left',
        titleOffsetX: Number(settings.title_offset_x || 0),
        titleOffsetY: Number(settings.title_offset_y || 0),
        subtitle: settings.subtitle || '',
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

import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import dayjs from 'dayjs';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}


export const formatDate = (data: Date, formato = 'DD/MM/YYYY') => data ? dayjs(data).locale('pt-br').format(formato) : '';

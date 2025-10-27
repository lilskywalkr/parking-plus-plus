import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { type Record } from '@/types';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function recordsArrayInsertSort<T extends Record>(
    direction: number,
    data: T[],
    propertyLabel: keyof T
) {
    const newArray = [...data];

    for (let i = 1; i < newArray.length; i++) {
        for (let j = i; j > 0; j--) {
            if (newArray[j - 1][propertyLabel] > newArray[j][propertyLabel]) {
                [newArray[j - 1], newArray[j]] = [newArray[j], newArray[j - 1]];
            }
        }
    }

    if (direction < 0) newArray.reverse();

    return newArray;
}

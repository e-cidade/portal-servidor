// src/utils/formatters.ts
export const formatCurrency = (value: string | number | null | undefined): string => {
  if (value === null || value === undefined) {
    return '';
  }
  const numericValue = parseFloat(value.toString());
  if (isNaN(numericValue)) {
    return ''; // Retorna string vazia para valores que não são números
  }
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(numericValue);
};
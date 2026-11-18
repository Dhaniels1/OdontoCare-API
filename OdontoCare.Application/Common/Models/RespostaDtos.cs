using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Common.Models;

public sealed record PaginaDto<T>(IReadOnlyList<T> Items, int Pagina, int Tamanho, int Total);

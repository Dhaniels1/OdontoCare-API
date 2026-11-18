using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Estoque.DTOs;

public sealed class MovimentacaoCreateDto
{
    [Range(1, int.MaxValue, ErrorMessage = "A quantidade deve ser positiva.")]
    public int Quantidade { get; set; }
}

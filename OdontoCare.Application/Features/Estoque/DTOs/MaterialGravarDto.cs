using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Estoque.DTOs;

public sealed class MaterialGravarDto
{
    [Required(ErrorMessage = "Nome obrigatório."), StringLength(200, MinimumLength = 2)]
    public string NomeMaterial { get; set; } = "";
    [MaxLength(2000)] public string? Descricao { get; set; }
    [Range(0, int.MaxValue, ErrorMessage = "Estoque mínimo não pode ser negativo.")]
    public int EstoqueMinimo { get; set; }
    [Required(ErrorMessage = "Unidade é obrigatória."), MaxLength(30)]
    public string Unidade { get; set; } = "";
}

using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Estoque.DTOs;

public sealed class EquipamentoGravarDto
{
    [Required, MaxLength(200)] public string NomeEquipamento { get; set; } = "";
    [MaxLength(2000)] public string? Descricao { get; set; }
    [MaxLength(50)] public string? StatusEquipamento { get; set; }
}

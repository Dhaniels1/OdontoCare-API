using OdontoCare.Application.Features.Financeiro.Validation;
using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.DTOs;

public sealed class OrcamentoGravarDto
{
    [Required, Dinheiro] public decimal? ValorTotal { get; set; }
    [Required, DataFinanceira] public DateTime? DataOrcamento { get; set; }
    [Required, RegularExpression("^(Pendente|Aprovado|Cancelado)$")] public string StatusOrcamento { get; set; } = "";
    [StringLength(16000)] public string? Observacoes { get; set; }
    [Range(1, int.MaxValue)] public int IdPaciente { get; set; }
}

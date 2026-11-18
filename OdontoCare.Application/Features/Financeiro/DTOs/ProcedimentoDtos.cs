using OdontoCare.Application.Features.Financeiro.Validation;
using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.DTOs;

public sealed class ProcedimentoGravarDto
{
    [Required, StringLength(200)] public string NomeProcedimento { get; set; } = "";
    [StringLength(16000)] public string? Descricao { get; set; }
    [Required, Dinheiro] public decimal? ValorProcedimento { get; set; }
}

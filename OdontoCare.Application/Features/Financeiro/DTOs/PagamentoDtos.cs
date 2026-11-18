using OdontoCare.Application.Features.Financeiro.Validation;
using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.DTOs;

public sealed class PagamentoGravarDto
{
    [Required, Dinheiro] public decimal? ValorTotal { get; set; }
    [Required, RegularExpression("^(Pix|Dinheiro|Cartão de Débito|Cartão de Crédito)$")] public string FormaPagamento { get; set; } = "";
    [Required, RegularExpression("^(Pendente|Pago|Cancelado)$")] public string StatusPagamento { get; set; } = "";
    [Required, DataFinanceira] public DateTime? DataPagamento { get; set; }
    [Range(1, int.MaxValue)] public int IdConsulta { get; set; }
}

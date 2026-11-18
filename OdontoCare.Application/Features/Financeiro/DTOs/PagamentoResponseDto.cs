using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.DTOs;

public sealed record PagamentoResponseDto(int IdPagamento, decimal ValorTotal, string? FormaPagamento,
    string? StatusPagamento, DateTime? DataPagamento, int IdConsulta, string NomePaciente, string NomeProcedimento);

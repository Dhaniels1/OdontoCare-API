using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.DTOs;

public sealed record OrcamentoResponseDto(int IdOrcamento, decimal ValorTotal, DateTime DataOrcamento,
    string? StatusOrcamento, string? Observacoes, int IdPaciente, string NomePaciente);

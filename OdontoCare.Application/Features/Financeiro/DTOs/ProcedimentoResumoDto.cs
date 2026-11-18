using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.DTOs;

public sealed record ProcedimentoResumoDto(string NomeProcedimento, int Total);

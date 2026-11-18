

namespace OdontoCare.Application.Features.Consultas.DTOs;

public sealed record ConsultaDto(int IdConsulta, DateTime DataConsulta, TimeSpan HorarioConsulta,
    string? StatusConsulta, string? Observacoes, int IdUsuario, int IdPaciente, int IdProcedimento);

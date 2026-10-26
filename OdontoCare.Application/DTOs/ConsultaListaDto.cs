namespace OdontoCare.Application.DTOs;
public sealed record ConsultaListaDto(int IdConsulta, DateTime DataConsulta, TimeSpan HorarioConsulta,
    string? StatusConsulta, string? Observacoes, int IdUsuario, int IdPaciente, int IdProcedimento,
    string NomeUsuario, string NomePaciente, string NomeProcedimento);
public sealed record ProcedimentoDto(int IdProcedimento, string NomeProcedimento);

namespace OdontoCare.Application.DTOs;

// Contratos de resposta: os controllers nunca expõem entidades do Domain.
public sealed record ConsultaDto(int IdConsulta, DateTime DataConsulta, TimeSpan HorarioConsulta,
    string? StatusConsulta, string? Observacoes, int IdUsuario, int IdPaciente, int IdProcedimento);
public sealed record ProcedimentoDetalheDto(int IdProcedimento, string NomeProcedimento,
    string? Descricao, decimal? ValorProcedimento);

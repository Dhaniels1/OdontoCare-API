using OdontoCare.Application.DTOs;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Mappings;

public static class EntityMappings
{
    public static ConsultaDto ToDto(this Consulta c) => new(c.IdConsulta, c.DataConsulta, c.HorarioConsulta,
        c.StatusConsulta, c.Observacoes, c.IdUsuario, c.IdPaciente, c.IdProcedimento);

    public static Consulta ToEntity(this ConsultaCreateDto d) => new()
    {
        DataConsulta = d.DataConsulta, HorarioConsulta = d.HorarioConsulta,
        StatusConsulta = d.StatusConsulta, Observacoes = d.Observacoes,
        IdUsuario = d.IdUsuario, IdPaciente = d.IdPaciente, IdProcedimento = d.IdProcedimento
    };

    public static Consulta ToEntity(this ConsultaUpdateDto d) => new()
    {
        DataConsulta = d.DataConsulta, HorarioConsulta = d.HorarioConsulta,
        StatusConsulta = d.StatusConsulta, Observacoes = d.Observacoes,
        IdUsuario = d.IdUsuario, IdPaciente = d.IdPaciente, IdProcedimento = d.IdProcedimento
    };

    public static ProcedimentoDetalheDto ToDto(this Procedimento p) =>
        new(p.IdProcedimento, p.NomeProcedimento, p.Descricao, p.ValorProcedimento);

    public static MaterialDto ToDto(this Material m) => new(m.IdMaterial, m.NomeMaterial, m.Descricao,
        m.Quantidade, m.EstoqueMinimo, m.Unidade, m.Quantidade <= m.EstoqueMinimo);

    public static PacienteResponseDto ToDto(this Paciente p) => new()
    {
        IdPaciente = p.IdPaciente, Nome = p.Nome, Cpf = p.Cpf, Telefone = p.Telefone,
        Email = p.Email, Cep = p.Cep, Logradouro = p.Logradouro, Numero = p.Numero,
        Bairro = p.Bairro, Cidade = p.Cidade, Estado = p.Estado,
        DataNascimento = p.DataNascimento
    };

    public static PacienteProntuarioDto ToProntuarioDto(this Paciente p) => new()
    {
        IdPaciente = p.IdPaciente, HistoricoOdontologico = p.HistoricoOdontologico,
        Alergias = p.Alergias, Exames = p.Exames,
        TratamentosAnteriores = p.TratamentosAnteriores,
        ProntuarioClinicoDigital = p.ProntuarioClinicoDigital
    };

    public static Paciente ToEntity(this PacienteCreateDto d) => new()
    {
        Nome = d.Nome.Trim(), Cpf = d.Cpf.Trim(), Telefone = d.Telefone, Email = d.Email,
        Cep = d.Cep, Logradouro = d.Logradouro, Numero = d.Numero,
        Bairro = d.Bairro, Cidade = d.Cidade, Estado = d.Estado,
        DataNascimento = d.DataNascimento, HistoricoOdontologico = d.HistoricoOdontologico,
        Alergias = d.Alergias, Exames = d.Exames,
        TratamentosAnteriores = d.TratamentosAnteriores,
        ProntuarioClinicoDigital = d.ProntuarioClinicoDigital
    };

    // O CPF é imutável após o cadastro: a atualização nunca o altera.
    public static void AplicarEm(this PacienteUpdateDto d, Paciente p, bool podeEditarProntuario)
    {
        p.Nome = d.Nome.Trim(); p.Telefone = d.Telefone; p.Email = d.Email;
        p.Cep = d.Cep; p.Logradouro = d.Logradouro; p.Numero = d.Numero;
        p.Bairro = d.Bairro; p.Cidade = d.Cidade; p.Estado = d.Estado;
        p.DataNascimento = d.DataNascimento;
        if (podeEditarProntuario)
        {
            p.HistoricoOdontologico = d.HistoricoOdontologico;
            p.Alergias = d.Alergias; p.Exames = d.Exames;
            p.TratamentosAnteriores = d.TratamentosAnteriores;
            p.ProntuarioClinicoDigital = d.ProntuarioClinicoDigital;
        }
    }
}

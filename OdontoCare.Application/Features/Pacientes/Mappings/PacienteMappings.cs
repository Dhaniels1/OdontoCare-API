using OdontoCare.Application.Features.Pacientes.DTOs;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Pacientes.Mappings;

public static class PacienteMappings
{
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

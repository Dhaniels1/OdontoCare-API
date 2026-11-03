using OdontoCare.Application.Common;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Services;

public class PacienteService : IPacienteService
{
    private readonly IEntityRepository<Paciente> pacientes;
    private readonly IEntityRepository<Consulta> consultas;
    private readonly IEntityRepository<Orcamento> orcamentos;
    public PacienteService(IEntityRepository<Paciente> pacientes, IEntityRepository<Consulta> consultas, IEntityRepository<Orcamento> orcamentos)
    { this.pacientes = pacientes; this.consultas = consultas; this.orcamentos = orcamentos; }

    public async Task<List<Paciente>> GetTodosAsync()
    {
        return await pacientes.ListarAsync();
    }

    public async Task<Paciente?> GetPorIdAsync(int id)
    {
        return await pacientes.BuscarAsync(p => p.IdPaciente == id);
    }

    public async Task<Paciente> CriarAsync(Paciente paciente)
    {
        pacientes.Adicionar(paciente);
        await pacientes.SalvarAsync();

        return paciente;
    }

    public async Task<bool> AtualizarAsync(int id, Paciente dados)
    {
        var paciente = await pacientes.BuscarAsync(p => p.IdPaciente == id);

        if (paciente == null)
            return false;

        paciente.Nome = dados.Nome;
        paciente.Telefone = dados.Telefone;
        paciente.Email = dados.Email;
        paciente.Cep = dados.Cep;
        paciente.Logradouro = dados.Logradouro;
        paciente.Numero = dados.Numero;
        paciente.Bairro = dados.Bairro;
        paciente.Cidade = dados.Cidade;
        paciente.Estado = dados.Estado;
        paciente.DataNascimento = dados.DataNascimento;
        paciente.HistoricoOdontologico = dados.HistoricoOdontologico;
        paciente.Alergias = dados.Alergias;
        paciente.Exames = dados.Exames;
        paciente.TratamentosAnteriores = dados.TratamentosAnteriores;
        paciente.ProntuarioClinicoDigital = dados.ProntuarioClinicoDigital;

        await pacientes.SalvarAsync();

        return true;
    }

    public async Task<bool> ExcluirAsync(int id)
    {
        var paciente = await pacientes.BuscarAsync(p => p.IdPaciente == id);

        if (paciente == null)
            return false;

        if (await consultas.ExisteAsync(c => c.IdPaciente == id) || await orcamentos.ExisteAsync(o => o.IdPaciente == id))
            throw new BusinessException("Paciente possui consultas ou orçamentos vinculados e não pode ser excluído.");

        pacientes.Remover(paciente);
        await pacientes.SalvarAsync();

        return true;
    }

    public async Task<bool> CpfExisteAsync(string cpf)
    {
        return await pacientes.ExisteAsync(p => p.Cpf == cpf);
    }
}

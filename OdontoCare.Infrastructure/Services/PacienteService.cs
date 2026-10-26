
using Microsoft.EntityFrameworkCore;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;
using OdontoCare.Infrastructure.Data;
using System.Data;

namespace OdontoCare.Infrastructure.Services;

public class PacienteService : IPacienteService
{
    private readonly OdontoCareDbContext _context;

    public PacienteService(OdontoCareDbContext context)
    {
        _context = context;
    }

    public async Task<List<Paciente>> GetTodosAsync()
    {
        return await _context.Pacientes
            .AsNoTracking()
            .ToListAsync();
    }

    public async Task<Paciente?> GetPorIdAsync(int id)
    {
        return await _context.Pacientes
            .FirstOrDefaultAsync(p => p.IdPaciente == id);
    }

    public async Task<Paciente> CriarAsync(Paciente paciente)
    {
        _context.Pacientes.Add(paciente);
        await _context.SaveChangesAsync();

        return paciente;
    }

    public async Task<bool> AtualizarAsync(int id, Paciente dados)
    {
        var paciente = await _context.Pacientes
            .FirstOrDefaultAsync(p => p.IdPaciente == id);

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

        await _context.SaveChangesAsync();

        return true;
    }

    public async Task<bool> ExcluirAsync(int id)
    {
        var paciente = await _context.Pacientes
            .FirstOrDefaultAsync(p => p.IdPaciente == id);

        if (paciente == null)
            return false;

        var conexao = _context.Database.GetDbConnection();
        var abriuConexao = conexao.State != ConnectionState.Open;

        if (abriuConexao)
            await conexao.OpenAsync();

        try
        {
            await using var comando = conexao.CreateCommand();

            comando.CommandText =
                "SELECT COUNT(*) FROM consulta WHERE idPaciente = @idPaciente";

            var parametro = comando.CreateParameter();
            parametro.ParameterName = "@idPaciente";
            parametro.Value = id;
            comando.Parameters.Add(parametro);

            var resultado = await comando.ExecuteScalarAsync();
            var possuiConsultas = Convert.ToInt64(resultado) > 0;

            if (possuiConsultas)
            {
                throw new InvalidOperationException(
                    "Não é possível excluir este paciente porque existem consultas vinculadas.");
            }
        }
        finally
        {
            if (abriuConexao)
                await conexao.CloseAsync();
        }

        _context.Pacientes.Remove(paciente);
        await _context.SaveChangesAsync();

        return true;
    }

    public async Task<bool> CpfExisteAsync(string cpf)
    {
        return await _context.Pacientes
            .AnyAsync(p => p.Cpf == cpf);
    }
}
